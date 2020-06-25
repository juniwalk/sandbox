<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Modules\WebModule;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Forms\Factory\AuthPasswordForgotFormFactory;
use App\Forms\Factory\AuthProfileFormFactory;
use App\Forms\Factory\AuthSignInFormFactory;
use App\Forms\Factory\AuthSignUpFormFactory;
use App\Forms\AuthPasswordForgotForm;
use App\Forms\AuthProfileForm;
use App\Forms\AuthSignInForm;
use App\Forms\AuthSignUpForm;
use App\Modules\AbstractPresenter;
use App\Security\AccessManager;
use Ramsey\Uuid\Uuid;

final class AuthPresenter extends AbstractPresenter
{
	/** @var AuthPasswordForgotFormFactory */
	private $authPasswordForgotFormFactory;

	/** @var AuthProfileFormFactory */
	private $authProfileFormFactory;

	/** @var AuthSignInFormFactory */
	private $authSignInFormFactory;

	/** @var AuthSignUpFormFactory */
	private $authSignUpFormFactory;

	/** @var UserRepository */
	private $userRepository;

	/** @var AccessManager */
	private $accessManager;

	/** @var User */
	private $user;


	/**
	 * @param AccessManager  $accessManager
	 * @param UserRepository  $userRepository
	 * @param AuthSignInFormFactory  $authSignInFormFactory
	 * @param AuthSignUpFormFactory  $authSignUpFormFactory
	 * @param AuthProfileFormFactory  $authProfileFormFactory
	 * @param AuthPasswordForgotFormFactory  $authPasswordForgotFormFactory
	 */
	public function __construct(
		AccessManager $accessManager,
		UserRepository $userRepository,
		AuthSignInFormFactory $authSignInFormFactory,
		AuthSignUpFormFactory $authSignUpFormFactory,
		AuthProfileFormFactory $authProfileFormFactory,
		AuthPasswordForgotFormFactory $authPasswordForgotFormFactory
	) {
		$this->authPasswordForgotFormFactory = $authPasswordForgotFormFactory;
		$this->authProfileFormFactory = $authProfileFormFactory;
		$this->authSignInFormFactory = $authSignInFormFactory;
		$this->authSignUpFormFactory = $authSignUpFormFactory;
		$this->userRepository = $userRepository;
		$this->accessManager = $accessManager;
	}


	/**
	 * @return void
	 */
	public function actionDefault(): void
	{
		if ($this->getUser()->isLoggedIn()) {
			$this->redirect(':Web:Home:default');
		}

		$this->redirect('signIn');
	}


	/**
	 * @return void
	 */
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
	 * @return PasswordForgotForm
	 */
	protected function createComponentPasswordForgotForm(string $name): PasswordForgotForm
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
	 * @return AuthProfileForm
	 */
	protected function createComponentProfileForm(string $name): AuthProfileForm
	{
		$user = $this->user ?: $this->getUser()->getIdentity();
		$form = $this->authProfileFormFactory->create($user);
		$form->onSuccess[] = function ($form, $data) {
			if ($hash = $this->getParameter('hash')) {
				$this->accessManager->clearToken($hash);
			}

			$this->redirect('default');
		};

		return $form;
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
}
