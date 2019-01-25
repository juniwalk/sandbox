<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Modules;

use JuniWalk\Tessa\TessaControl;
use Nette\Application\UI\Presenter;
use Nette\Security\IUserStorage;
use Nette\Utils\Strings;

abstract class AbstractPresenter extends Presenter
{
    /** @var TessaControl */
    private $tessa;


	/**
	 * @param  TessaControl  $tessa
	 * @return void
	 */
	public function injectTessaControl(TessaControl $tessa): void
	{
        $this->tessa = $tessa;
	}


	/**
	 * @return bool
	 */
	public function hasFlashMessages(): bool
	{
		$flashSession = $this->getPresenter()->getFlashSession();
		$id = $this->getParameterId('flash');

		return !empty($flashSession->$id);
	}


    /**
     * @return string
     */
    public function getPageName(): string
    {
        return $this->getName().':'.$this->getAction();
    }


	/**
	 * @throws ForbiddenRequestException
	 */
	protected function startup()
	{
		$user = $this->getUser();
        $profile = $user->getIdentity();

		if (!$user->isLoggedIn() && !$user->isAllowed($this->getName(), $this->getAction())) {
			if ($user->getLogoutReason() === IUserStorage::INACTIVITY) {
				$this->flashMessage('nette.message.auth-signout', 'warning');
			}

			$this->redirect(':Auth:signIn', ['redirect' => $this->storeRequest()]);
		}

		if (!$user->isAllowed($this->getName(), $this->getAction()) || ($profile && !$profile->isActive())) {
			throw new ForbiddenRequestException('You don\'t have access to '.$this->getPageName().'!', 403);
		}

		return parent::startup();
	}


	protected function beforeRender()
	{
		if ($this->hasFlashMessages() && !$this->isControlInvalid()) {
			$this->redrawControl('flashMessages');
		}

		$template = $this->getTemplate();
		$template->add('appDir', $this->getContext()->parameters['appDir']);
		$template->add('profile', $this->getUser()->getIdentity());
		$template->add('pageName', Strings::webalize($this->getPageName()));
		$template->add('flashIcon', [
			'success' => 'fa-check-circle',
			'warning' => 'fa-exclamation-circle',
			'danger' => 'fa-times-circle',
			'info' => 'fa-info-circle',
		]);

		return parent::beforeRender();
	}


    /**
     * @param  string  $name
     * @return TessaControl
     */
    protected function createComponentTessa(string $name): TessaControl
    {
        return $this->tessa;
    }
}
