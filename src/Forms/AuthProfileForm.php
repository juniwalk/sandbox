<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use App\Managers\MessageManager;
use App\Managers\UserManager;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;
use JuniWalk\Form\Renderer;

final class AuthProfileForm extends AbstractForm
{
	/** @var MessageManager */
	private $messageManager;

	/** @var EntityManager */
	private $entityManager;

	/** @var UserManager */
	private $userManager;

	/** @var User */
	private $user;


	/**
	 * @param User  $user
	 * @param UserManager  $userManager
	 * @param EntityManager  $entityManager
	 * @param MessageManager  $messageManager
	 */
	public function __construct(
		User $user,
		UserManager $userManager,
		EntityManager $entityManager,
		MessageManager $messageManager
	) {
		$this->messageManager = $messageManager;
		$this->entityManager = $entityManager;
		$this->userManager = $userManager;
		$this->user = $user;

		$this->setTemplateFile(__DIR__.'/templates/authProfileForm.latte');
		$this->onBeforeRender[] = function ($form, $template) {
			$template->add('profile', $this->user);
			$this->setDefaults($this->user);
		};
	}


	/**
	 * @return User
	 */
	public function getUser(): User
	{
		return $this->user;
	}


	/**
	 * @param  User  $user
	 * @return void
	 */
	public function setDefaults(User $user): void
	{
		$form = $this->getForm();
		$form->setDefaults([
			'name' => $user->getName(),
			'email' => $user->getEmail(),
		]);
	}


	/**
	 * @return void
	 * @secured
	 */
	public function handleResendActivationEmail(): void
	{
		$presenter = $this->getPresenter();
		$user = $this->getUser();

		if ($user->isEmailActivated()) {
			$this->redirect('this');
		}

		$this->userManager->deactivateUser($user);
		$this->entityManager->flush();

		$this->messageManager->sendUserSignUpMessage($user);

		$presenter->flashMessage('web.message.auth-email-sent', 'success');
		$presenter->redrawControl('flashMessages');
	}


	/**
	 * @param  string  $name
	 * @return Form
	 */
	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addText('name');
		$form->addText('email')->setRequired('nette.user.email-required')->setType('email');
		$form->addPassword('password')->addCondition($form::FILLED)
			->addRule($form::MIN_LENGTH, 'nette.user.password-length', 6);

        $form->addSubmit('submit');

		return $form;
	}


    /**
     * @param  Form  $form
     * @param  ArrayHash  $data
	 * @return void
     */
    protected function handleSuccess(Form $form, ArrayHash $data): void
    {
    	$user = $this->getUser();
		$user->setName($data->name);
		$user->setEmail($data->email);

		if (!empty($data->password)) {
			$user->setPassword($data->password);
		}

		try {
			$this->entityManager->flush();

		} catch(UniqueConstraintViolationException $e) {
			$form['email']->addError('nette.message.auth-email-used');
		}
    }
}
