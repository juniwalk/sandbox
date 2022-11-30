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
use Nette\Application\Attributes\Persistent;
use Nette\Application\BadRequestException;
use Nette\Application\ForbiddenRequestException;

final class AuthPresenter extends AbstractPresenter
{
	private ?User $user = null;

	#[Persistent]
	public string $redirect = '';


	public function __construct(
		private UserManager $userManager,
		private AccessManager $accessManager,
		private UserRepository $userRepository,
		private AuthSignInFormFactory $authSignInFormFactory,
		private AuthSignUpFormFactory $authSignUpFormFactory,
		private AuthProfileFormFactory $authProfileFormFactory,
		private AuthPasswordForgotFormFactory $authPasswordForgotFormFactory
	) {}


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


	public function actionSignOut(): void
	{
		$this->getUser()->logout(true);
		$this->getSession()->destroy();
		$this->redirectAjax('default');
	}


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


	protected function createComponentPasswordForgotForm(): AuthPasswordForgotForm
	{
		$form = $this->authPasswordForgotFormFactory->create();
		$form->onSuccess[] = function() {
			$this->flashMessage('web.message.auth-email-sent', 'success');
			$this->redirectAjax('signIn');
		};

		return $form;
	}


	protected function createComponentProfileForm(): AuthProfileForm
	{
		$user = $this->user ?: $this->getUser()->getIdentity();
		$form = $this->authProfileFormFactory->create($user);
		$form->onSuccess[] = function() {
			if ($hash = $this->getParameter('hash')) {
				$this->accessManager->clearToken($hash);
			}

			$this->redirectAjax('default');
		};

		return $form;
	}


	protected function createComponentSignInForm(string $name): AuthSignInForm
	{
		$form = $this->authSignInFormFactory->create($name);
		$form->onSuccess[] = function() {
			$this->restoreRequest($this->redirect);
			$this->redirectAjax('default');
		};

		return $form;
	}


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
