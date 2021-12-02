<?php declare(strict_types=1);

/**
 * @copyright Martin Procházka (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\UserRepository;
use App\Managers\UserManager;
use JuniWalk\Form\AbstractForm;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;

final class AuthPasswordForgotForm extends AbstractForm
{
	/** @var UserRepository */
	private $userRepository;

	/** @var UserManager */
	private $userManager;


    /**
	 * @param UserManager  $userManager
     * @param UserRepository  $userRepository
     */
    public function __construct(
		UserManager $userManager,
		UserRepository $userRepository
	) {
		$this->userRepository = $userRepository;
		$this->userManager = $userManager;
    }


	/**
	 * @param  string  $name
	 * @return Form
	 */
	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
        $form->addText('email')->setRequired('nette.user.email-required')
            ->addRule($form::EMAIL, 'nette.user.email-invalid');
		$form->addReCaptcha('recaptcha')->setRequired('nette.user.captcha-required');

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
    	try {
			$user = $this->userRepository->getByEmail($data->email);
			$this->userManager->passwordForgot($user);

		// also catch exception from manager
		} catch (BadRequestException $e) {
			$form['email']->addError('nette.message.auth-email-unknown');
		}
    }
}
