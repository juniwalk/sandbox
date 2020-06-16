<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Modules;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Forms\Factory\AuthPasswordForgotFormFactory;
use App\Forms\Factory\AuthSignInFormFactory;
use App\Forms\Factory\AuthSignUpFormFactory;
use App\Forms\Factory\ProfileFormFactory;
use App\Forms\AuthSignInForm;
use App\Forms\AuthSignUpForm;
use App\Security\AccessManager;
use Ramsey\Uuid\Uuid;

final class AuthPresenter extends AbstractPresenter
{
	/** @var AuthPasswordForgotFormFactory */
	private $authPasswordForgotFormFactory;

	/** @var AuthSignInFormFactory */
	private $authSignInFormFactory;

	/** @var AuthSignUpFormFactory */
	private $authSignUpFormFactory;

	/** @var ProfileFormFactory */
	private $profileFormFactory;

	/** @var UserRepository */
	private $userRepository;

	/** @var AccessManager */
	private $accessManager;

	/** @var User */
	private $user;


	/**
	 * @param AccessManager  $accessManager
	 * @param UserRepository  $userRepository
	 * @param ProfileFormFactory  $profileFormFactory
	 * @param AuthSignInFormFactory  $authSignInFormFactory
	 * @param AuthSignUpFormFactory  $authSignUpFormFactory
	 * @param AuthPasswordForgotFormFactory  $authPasswordForgotFormFactory
	 */
	public function __construct(
		AccessManager $accessManager,
		UserRepository $userRepository,
		ProfileFormFactory $profileFormFactory,
		AuthSignInFormFactory $authSignInFormFactory,
		AuthSignUpFormFactory $authSignUpFormFactory,
		AuthPasswordForgotFormFactory $authPasswordForgotFormFactory
	) {
		$this->authPasswordForgotFormFactory = $authPasswordForgotFormFactory;
		$this->authSignInFormFactory = $authSignInFormFactory;
		$this->authSignUpFormFactory = $authSignUpFormFactory;
		$this->profileFormFactory = $profileFormFactory;
		$this->userRepository = $userRepository;
		$this->accessManager = $accessManager;
	}


	public function actionDefault(): void
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->redirect(':Homepage:default');
		}

		$this->redirect('signIn');
	}


	public function actionSignOut(): void
	{
		$this->getUser()->logout(true);
		$this->redirect('signIn');
	}


	/**
	 * @param  string|null  $hash
	 * @return void
	 */
	public function actionProfile(string $hash = null): void
	{
		if (!$hash && !$this->getUser()->isLoggedIn()) {
			$this->redirect('signIn');
		}

		if ($hash && Uuid::isValid($hash)) {
			$data = $this->accessManager->validateToken($hash, false);
			$this->user = $this->userRepository->getById((int) $data['key']);
		}
	}


	/**
	 * @param  string  $name
	 * @return AuthSignInForm
	 */
	protected function createComponentSignInForm(string $name): AuthSignInForm
	{
		$form = $this->authSignInFormFactory->create($name);
		$form->onSuccess[] = function ($form, $data) {
			$this->redirect('default');
		};

		return $form;
	}


	/**
	 * @param  string  $name
	 * @return AuthSignUpForm
	 */
	protected function createComponentSignUpForm(string $name): AuthSignUpForm
	{
		$form = $this->authSignUpFormFactory->create($name);
		$form->onSuccess[] = function ($form, $data) {
			$this->getUser()->login($data->email, $data->password);
			$this->redirect('default');
		};

		return $form;
	}


	/**
	 * @param  string  $name
	 * @return PasswordForgotForm
	 */
	protected function createComponentPasswordForgotForm(string $name)
	{
		$form = $this->authPasswordForgotFormFactory->create();
		$form->onSuccess[] = function ($form, $data) {
			$this->flashMessage('nette.message.auth-email-sent', 'success');
			$this->redirect('signIn');
		};

		return $form;
	}


	/**
	 * @param  string  $name
	 * @return ProfileForm
	 */
	protected function createComponentProfileForm(string $name)
	{
		$user = $this->user ?: $this->getUser()->getIdentity();
		$form = $this->profileFormFactory->create($user);
		$form->onSuccess[] = function ($form, $data) {
			if ($token = $this->getParam('hash')) {
				$this->accessManager->clearToken($token);
			}

			$this->redirect('default');
		};

		return $form;
	}
}
