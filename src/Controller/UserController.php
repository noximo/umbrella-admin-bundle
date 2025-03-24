<?php

namespace Umbrella\AdminBundle\Controller;

use Symfony\Component\HttpFoundation\Request;
use Symfony\Component\HttpFoundation\Response;

use function Symfony\Component\Translation\t;

use Umbrella\AdminBundle\Lib\Controller\AdminController;
use Umbrella\AdminBundle\Service\UserManagerInterface;
use Umbrella\AdminBundle\UmbrellaAdminConfiguration;

class UserController extends AdminController
{
    public function __construct(
        private readonly UmbrellaAdminConfiguration $config,
        private readonly UserManagerInterface $userManager,
    ) {
    }

    public function index(Request $request): Response
    {
        $table = $this->createTable($this->config->userTable());
        $table->handleRequest($request);

        if ($table->isCallback()) {
            return $table->getCallbackResponse();
        }

        return $this->render('@UmbrellaAdmin/datatable.html.twig', [
            'table' => $table,
        ]);
    }

    public function edit(Request $request, ?int $id = null): Response
    {
        if (null === $id) {
            $entity = $this->userManager->create();
        } else {
            $entity = $this->userManager->find($id);
            $this->throwNotFoundExceptionIfNull($entity);
        }

        $form = $this->createForm($this->config->userForm(), $entity, [
            'password_required' => null === $id,
        ]);

        $form->handleRequest($request);

        if ($form->isSubmitted() && $form->isValid()) {
            $this->userManager->updatePassword($entity);
            $this->userManager->save($entity);

            return $this->js()
                ->closeModal()
                ->reloadTable()
                ->toastSuccess(t('message.item_updated', [], 'UmbrellaAdmin'));
        }

        return $this->js()
            ->modal('@UmbrellaAdmin/user/edit.html.twig', [
                'form' => $form->createView(),
                'entity' => $entity,
            ]);
    }

    public function delete(int $id): Response
    {
        $entity = $this->userManager->find($id);
        $this->throwNotFoundExceptionIfNull($entity);

        $this->userManager->delete($entity);

        return $this->js()
            ->closeModal()
            ->reloadTable()
            ->toastSuccess(t('message.item_deleted', [], 'UmbrellaAdmin'));
    }
}
