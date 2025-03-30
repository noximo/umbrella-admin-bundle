<?= "<?php\n" ?>

namespace <?= $namespace ?>;

use <?= $repository->getFullName() ?>;
use Doctrine\ORM\Mapping as ORM;
use Umbrella\AdminBundle\Entity\Trait\IdTrait;

#[ORM\Entity(repositoryClass: <?= $repository->getShortName() ?>::class)]
class <?= $class_name."\n" ?>
{
    use IdTrait;
}
