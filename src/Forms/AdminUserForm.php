<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\Enums\Role;
use App\Entity\User;
use App\Managers\UserManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;

final class AdminUserForm extends AbstractForm
{
	/** @var EntityManager */
	private $entityManager;

	/** @var UserManager */
	private $userManager;

	/** @var User */
	private $user;


	/**
	 * @param User|null  $user
	 * @param UserManager  $userManager
	 * @param EntityManager  $entityManager
	 */
	public function __construct(
		?User $user,
		UserManager $userManager,
		EntityManager $entityManager
	) {
		$this->entityManager = $entityManager;
		$this->userManager = $userManager;
		$this->user = $user;

		$this->onBeforeRender[] = function ($form, $template) {
			$template->add('profile', $this->user);
			$this->setDefaults($this->user);
		};
	}


	/**
	 * @return void
	 */
	public function handlePasswordForgot(): void
	{
		$presenter = $this->getPresenter();
		$user = $this->getUser();

    	try {
			$this->userManager->passwordForgot($user);

		// invalid catch
		} catch (BadRequestException $e) {
			$presenter->flashMessage('nette.message.auth-email-unknown', 'danger');
		}

		$presenter->redrawControl('flashMessages');
		$this->redrawControl('form');
	}


	/**
	 * @return User|null
	 */
	public function getUser(): ?User
	{
		return $this->user;
	}


	/**
	 * @param  User|null  $user
	 * @return void
	 */
	public function setDefaults(?User $user): void
	{
		$form = $this->getForm();

		if (!isset($user)) {
			return;
		}

		$form->setDefaults([
			'name' => $user->getName(),
			'email' => $user->getEmail(),
			'role' => $user->getRole(),
			'active' => $user->isActive(),
		]);
	}


	/**
	 * @param  string  $name
	 * @return Form
	 */
	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addText('name');
		$form->addText('email')->setType('email')
			->setRequired('web.user.email-required');
		$form->addSelect('role')->setItems((new Role)->getItems())
			->setRequired('web.user.role-required');
		$form->addCheckbox('active');

        $form->addSubmit('submit');
        $form->addSubmit('apply');

		return $form;
	}


    /**
     * @param  Form  $form
     * @param  ArrayHash  $data
	 * @return void
     */
    protected function handleSuccess(Form $form, ArrayHash $data): void
    {
		$user = $this->getUser() ?: new User($data->email, $data->name);
		$user->setName($data->name);
		$user->setEmail($data->email);
		$user->setRole($data->role);
		$user->setActive($data->active);

		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();
			$this->user = $user;

		} catch(UniqueConstraintViolationException $e) {
			$form['email']->addError('nette.message.auth-email-used');

		} catch(ORMException $e) {
			$form->addError('nette.message.something-went-wrong');
		}
    }
}
