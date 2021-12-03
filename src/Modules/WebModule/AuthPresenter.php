<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
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
use App\Managers\UserManager;
use App\Managers\AccessManager;
use App\Modules\AbstractPresenter;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

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

	/** @var UserManager */
	private $userManager;

	/** @var User */
	private $user;

	/** @var string @persistent */
	public $redirect = '';


	/**
	 * @param UserManager  $userManager
	 * @param AccessManager  $accessManager
	 * @param UserRepository  $userRepository
	 * @param AuthSignInFormFactory  $authSignInFormFactory
	 * @param AuthSignUpFormFactory  $authSignUpFormFactory
	 * @param AuthProfileFormFactory  $authProfileFormFactory
	 * @param AuthPasswordForgotFormFactory  $authPasswordForgotFormFactory
	 */
	public function __construct(
		UserManager $userManager,
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
		$this->userManager = $userManager;
	}


	/**
	 * @return void
	 */
	public function actionDefault(): void
	{
		$user = $this->getUser();

		if ($user->isLoggedIn() && $user->isAllowed('Admin:Home')) {
			$this->redirectAjax(':Admin:Home:default');
		}

		if ($user->isLoggedIn()) {
			$this->redirectAjax(':Web:Home:default', ['userMenu' => true]);
		}

		$this->redirectAjax('signIn');
	}


	/**
	 * @return void
	 */
	public function actionSignOut(): void
	{
		$this->getUser()->logout(true);
		$this->getSession()->destroy();
		$this->redirectAjax(':Web:Home:default');
	}


	/**
	 * @param  string|null  $hash
	 * @return void
	 */
	public function actionProfile(string $hash = null): void
	{
		if (!$hash && !$this->getUser()->isLoggedIn()) {
			$this->redirectAjax('signIn');
		}

		if ($hash) {
			$this->user = $this->accessManager->validateSluggedToken('Web:Auth:profile', $hash, false);
		}
	}


	/**
	 * @param  string  $hash
	 * @return void
	 * @throws ForbiddenRequestException
	 * @throws BadRequestException
	 */
	public function actionActivate(string $hash): void
	{
		$data = $this->accessManager->validateToken($hash, false);

		if ($data['_slug'] !== 'Web:Auth:activate') {
			throw new ForbiddenRequestException('Slug missmatch, expected '.$data['_slug'].' but '.$slug.' was given');
		}

		$user = $this->userRepository->getByEmail($data['key']);
		$this->userManager->activateUser($user, $hash);

		$this->getUser()->login($user);
		$this->flashMessage('web.message.auth-activated', 'success');
		$this->redirectAjax(':Web:Auth:default');
	}


	/**
	 * @return void
	 */
	protected function beforeRender()
	{
		$profile = $this->getUser()->getIdentity();

		if ($profile && !$profile->isEmailActivated()) {
			$this->flashMessage('web.message.auth-not-activated', 'warning');
		}

		return parent::beforeRender();
	}


	/**
	 * @param  string  $name
	 * @return AuthPasswordForgotForm
	 */
	protected function createComponentPasswordForgotForm(string $name): AuthPasswordForgotForm
	{
		$form = $this->authPasswordForgotFormFactory->create();
		$form->onSuccess[] = function($form, $data) {
			$this->flashMessage('web.message.auth-email-sent', 'success');
			$this->redirectAjax('signIn');
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
		$form->onSuccess[] = function($form, $data) {
			if ($hash = $this->getParameter('hash')) {
				$this->accessManager->clearToken($hash);
			}

			$this->redirectAjax('default');
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
		$form->onSuccess[] = function($form, $data) {
			$this->restoreRequest($this->redirect);
			$this->redirectAjax('default');
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
		$form->onSuccess[] = function($form, $data) {
			$this->getUser()->login($data->email, $data->password);
			$this->redirectAjax('default');
		};

		return $form;
	}
}
