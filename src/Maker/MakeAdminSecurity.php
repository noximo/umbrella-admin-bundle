<?php

namespace Umbrella\AdminBundle\Maker;

use Symfony\Bundle\MakerBundle\ConsoleStyle;
use Symfony\Bundle\MakerBundle\DependencyBuilder;
use Symfony\Bundle\MakerBundle\Generator;
use Symfony\Bundle\MakerBundle\InputConfiguration;
use Symfony\Bundle\MakerBundle\Maker\AbstractMaker;
use Symfony\Bundle\MakerBundle\Util\YamlSourceManipulator;
use Symfony\Component\Console\Command\Command;
use Symfony\Component\Console\Input\InputInterface;
use Symfony\Component\Console\Style\SymfonyStyle;
use Umbrella\AdminBundle\Maker\Utils\MakeHelper;

class MakeAdminSecurity extends AbstractMaker
{
    private const NAME = 'make:admin:security';
    private const DESCRIPTION = 'Configure security for admin';

    public function __construct(private readonly MakeHelper $helper)
    {
    }

    public static function getCommandName(): string
    {
        return self::NAME;
    }

    public static function getCommandDescription(): string
    {
        return self::DESCRIPTION;
    }

    public function configureCommand(Command $command, InputConfiguration $inputConfig): void
    {
    }

    public function configureDependencies(DependencyBuilder $dependencies): void
    {
    }

    public function interact(InputInterface $input, ConsoleStyle $io, Command $command): void
    {
    }

    public function generate(InputInterface $input, ConsoleStyle $io, Generator $generator): void
    {
        $entityClass = $this->helper->askEntityClass($io, 'AdminUser');

        $entity = $generator->createClassNameDetails($entityClass, 'Entity\\');
        $repository = $generator->createClassNameDetails($entityClass, 'Repository\\', 'Repository');

        $vars = [
            'entity' => $entity,
            'repository' => $repository
        ];

        $generator->generateClass(
            $entity->getFullName(),
            $this->helper->template('AdminUser.tpl.php'),
            $vars
        );
        $generator->generateClass(
            $repository->getFullName(),
            $this->helper->template('EntityRepository.tpl.php'),
            $vars
        );

        $generator->writeChanges();
        $this->updateRouteConfig($io);
        $this->updateUserConfig($io, $entity->getFullName());
        $this->updateSecurityConfig($io, $entity->getFullName());
        $this->writeSuccessMessage($io);

        $io->writeln('');
        $io->writeln('Read more about it on <href=https://acantepie.github.io/umbrella-admin-bundle/#/getting-started/manage_user_with_doctrine>Documentation</>');
        $io->writeln('');
    }

    private function updateRouteConfig(SymfonyStyle $io): void
    {
        $configPath = 'config/routes.yaml';

        if (!$this->helper->fileExists($configPath)) {
            $io->warning('The file "config/routes.yaml" does not exist. PHP & XML configuration formats are currently not supported. You have to register routes manually :');
            $io->text([
                'umbrella_admin_profile_:',
                '    resource: \'@UmbrellaAdminBundle/config/routes/profile.php\'',
                '    prefix: /admin',
                '',
                'umbrella_admin_user_:',
                '    resource: \'@UmbrellaAdminBundle/config/routes/user.php\'',
                '    prefix: /admin',
                '',
                'umbrella_admin_security_:',
                '    resource: \'@UmbrellaAdminBundle/config/routes/security.php\'',
                '    prefix: /admin',
                ''
            ]);

            return;
        }

        $manipulator = new YamlSourceManipulator($this->helper->getFileContents($configPath));
        $data = $manipulator->getData();

        $data['umbrella_admin_profile_'] = [
            'resource' => '@UmbrellaAdminBundle/config/routes/profile.php',
            'prefix' => '/admin'
        ];
        $data['umbrella_admin_user_'] = [
            'resource' => '@UmbrellaAdminBundle/config/routes/user.php',
            'prefix' => '/admin'
        ];
        $data['umbrella_admin_security_'] = [
            'resource' => '@UmbrellaAdminBundle/config/routes/security.php',
            'prefix' => '/admin'
        ];

        $manipulator->setData($data);
        $this->helper->writeFileContents($configPath, $manipulator->getContents());

        $io->writeln(\sprintf(' <fg=yellow>updated</>: %s', $configPath));
    }

    private function updateUserConfig(SymfonyStyle $io, string $userClass): void
    {
        $configPath = 'config/packages/umbrella_admin.yaml';

        $configContent = $this->helper->fileExists($configPath)
            ? $this->helper->getFileContents($configPath)
            : 'umbrella_admin:';

        $manipulator = new YamlSourceManipulator($configContent);
        $data = $manipulator->getData();
        $data['umbrella_admin']['user']['class'] = $userClass;

        $manipulator->setData($data);
        $this->helper->writeFileContents($configPath, $manipulator->getContents());

        $io->writeln(\sprintf(' <fg=yellow>updated</>: %s', $configPath));
    }

    private function updateSecurityConfig(SymfonyStyle $io, string $userClass): void
    {
        $configPath = 'config/packages/security.yaml';

        if (!$this->helper->fileExists($configPath)) {
            $io->warning('The file "config/packages/security.yaml" does not exist. PHP & XML configuration formats are currently not supported. You have to configure security manually.');
            return;
        }

        $manipulator = new YamlSourceManipulator($this->helper->getFileContents($configPath));
        $data = $manipulator->getData();

        // password hashers
        $data['security']['password_hashers'][$userClass] = 'auto';

        // provider
        $data['security']['providers']['admin_entity_provider']['entity'] = [
            'class' => $userClass,
            'property' => 'email'
        ];

        // firewall
        $data['security']['firewalls']['admin'] = [
            'pattern' => '^/admin',
            'user_checker' => 'Umbrella\AdminBundle\Security\UserChecker',
            'entry_point' => 'Umbrella\AdminBundle\Security\AuthenticationEntryPoint',
            'provider' => 'admin_entity_provider',
            'lazy' => true,
            'form_login' => [
                'login_path' => 'umbrella_admin_login',
                'check_path' => 'umbrella_admin_login',
                'default_target_path' => 'app_admin_home_index',
                'enable_csrf' => true
            ],
            'logout' => [
                'path' => 'umbrella_admin_logout',
                'target' => 'umbrella_admin_login'
            ]
        ];

        // access control
        $data['security']['access_control'] = [
            ['path' => '^/admin/login$', 'roles' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/password_request', 'roles' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin/password_reset', 'roles' => 'PUBLIC_ACCESS'],
            ['path' => '^/admin', 'roles' => 'ROLE_ADMIN'],
        ];

        $manipulator->setData($data);
        $this->helper->writeFileContents($configPath, $manipulator->getContents());

        $io->writeln(\sprintf(' <fg=yellow>updated</>: %s', $configPath));
    }
}
