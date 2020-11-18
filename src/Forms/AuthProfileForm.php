<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use App\Managers\MessageManager;
use App\Managers\UserManager;
use Contributte\ImageStorage\ImageStorage;
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

	/** @var ImageStorage */
	private $imageStorage;

	/** @var UserManager */
	private $userManager;

	/** @var User */
	private $user;


	/**
	 * @param User  $user
	 * @param UserManager  $userManager
	 * @param ImageStorage  $imageStorage
	 * @param EntityManager  $entityManager
	 * @param MessageManager  $messageManager
	 */
	public function __construct(
		User $user,
		UserManager $userManager,
		ImageStorage $imageStorage,
		EntityManager $entityManager,
		MessageManager $messageManager
	) {
		$this->messageManager = $messageManager;
		$this->entityManager = $entityManager;
		$this->imageStorage = $imageStorage;
		$this->userManager = $userManager;
		$this->user = $user;

		$this->setTemplateFile(__DIR__.'/templates/authProfileForm.latte');
		$this->onBeforeRender[] = function ($form, $template) {
			$template->add('imageStorage', $this->imageStorage);
			$template->add('profile', $this->user);
			$this->setDefaults($this->user);
		};
	}


	/**
	 * @return void
	 */
	public function handleImageRemove(): void
	{
		$presenter = $this->getPresenter();
    	$user = $this->getUser();

		try {
			$this->userManager->imageRemove($user);

		// invalid catch
		} catch(UniqueConstraintViolationException $e) {
			$presenter->flashMessage('nette.message.email-taken', 'danger');
		}

		$presenter->redrawControl('flashMessages');
		$this->redrawControl('form');
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
		$form->addUpload('image')->addCondition($form::FILLED)
			->addRule($form::MAX_FILE_SIZE, 'nette.user.image-too-large', 2097152)
			->addRule($form::IMAGE, 'nette.user.image-invalid');
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

		if ($data->image->isOk()) {
			if ($user->hasImage()) {
				$this->imageStorage->delete($user->getImage());
			}

			$image = $this->imageStorage->saveUpload($data->image, 'avatar');
			$user->setImage($image);
		}

		try {
			$this->entityManager->flush();

		} catch(UniqueConstraintViolationException $e) {
			$form['email']->addError('nette.message.auth-email-used');
		}
    }
}
