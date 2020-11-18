<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Modules;

use App\Bootstrap;
use App\Modules\WebModule\AuthPresenter;
use Contributte\Translation\LocalesResolvers\Session as SessionResolver;
use JuniWalk\Tessa\BundleManager;
use JuniWalk\Tessa\TessaControl;
use Nette\Application\UI\Presenter;
use Nette\Localization\ITranslator as Translator;
use Nette\Security\IUserStorage;
use Nette\Utils\Strings;

abstract class AbstractPresenter extends Presenter
{
    /** @var SessionResolver */
	private $sessionResolver;

    /** @var BundleManager */
    private $bundleManager;

    /** @var Translator */
    private $translator;

    /** @var string @persistent */
    public $locale;


	/**
	 * @param  BundleManager  $bundleManager
	 * @return void
	 */
	public function injectBundleManager(BundleManager $bundleManager): void
	{
        $this->bundleManager = $bundleManager;
	}


	/**
	 * @param  SessionResolver  $sessionResolver
	 * @return void
	 */
	public function injectSessionResolver(SessionResolver $sessionResolver): void
	{
        $this->sessionResolver = $sessionResolver;
	}


	/**
	 * @param  Translator  $translator
	 * @return void
	 */
	public function injectTranslator(Translator $translator): void
	{
        $this->translator = $translator;
	}

	
	/**
	 * @param  string  $lang
	 * @return void
	 */
	public function handleLocale(string $lang): void
	{
        $this->sessionResolver->setLocale($this->locale = $lang);
		$this->redirect('this');
	}


	/**
	 * @param  string  $modal
	 * @param  mixed[]  $params
	 * @return void
	 */
	public function openModal(string $modal, iterable $params = []): void
	{
		$template = $this->getTemplate();
		$template->add('openModal', '#'.$modal);

		foreach ($params as $key => $value) {
			$template->add($key, $value);
		}

		$this->redrawControl('modals');
	}


	/**
	 * @throws ForbiddenRequestException
	 * @return void
	 */
	protected function startup()
	{
		$user = $this->getUser();

		if (!$user->isLoggedIn() && !$user->isAllowed($this->getName(), $this->getAction())) {
			if ($user->getLogoutReason() === IUserStorage::INACTIVITY) {
				$this->flashMessage('web.message.auth-signout', 'warning');
			}

			$this->redirect(':Web:Auth:signIn', ['redirect' => $this->storeRequest()]);
		}

		if (!$user->isAllowed($this->getName(), $this->getAction())) {
			throw new ForbiddenRequestException('You don\'t have access to '.$this->getAction(true).'!', 403);
		}

        $profile = $user->getIdentity();

		if ($profile && !$profile->isEmailActivated() && !$this instanceof AuthPresenter) {
			$this->flashMessage('web.message.auth-not-activated', 'warning');
			$this->redirect(':Web:Auth:profile');
		}

		if ($profile && !$profile->isActive() && !$this instanceof AuthPresenter) {
			$this->flashMessage('web.message.auth-banned', 'warning');
			$this->redirect(':Web:Auth:signOut');
		}

		if ($this->isModuleCurrent('Admin') && !Bootstrap::isDebugMode()) {
			throw new ForbiddenRequestException('You don\'t have access to '.$this->getAction(true).'!', 403);
		}

		return parent::startup();
	}


	/**
	 * @return void
	 */
	protected function beforeRender()
	{
		$locale = $this->translator->getLocale();
		$locales = [];

		foreach ($this->translator->getLocalesWhitelist() as $lang) {
			$locales[$lang] = 'web.enum.locale.'.$lang;
		}

		if (!isset($locales[$locale])) {
			$locale = $this->translator->getDefaultLocale();
		}

		$template = $this->getTemplate();
		$template->add('pageName', Strings::webalize($this->getAction(true)));
		$template->add('appDir', $this->getContext()->parameters['appDir']);
		$template->add('profile', $this->getUser()->getIdentity());
		$template->add('isLocked', Bootstrap::isLocked());
		$template->add('locales', $locales);
		$template->add('locale', $locale);

		return parent::beforeRender();
	}


    /**
     * @param  string  $name
     * @return TessaControl
     */
    protected function createComponentTessa(string $name): TessaControl
    {
        return new TessaControl($this->bundleManager);
    }
}
