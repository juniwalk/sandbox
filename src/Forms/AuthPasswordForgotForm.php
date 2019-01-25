<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\UserRepository;
use App\Messages\PasswordForgotMessage;
use App\Security\AccessManager;
use JuniWalk\Form\AbstractForm;
use Nette\Application\BadRequestException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use Ublaboo\Mailing\MailFactory;

final class AuthPasswordForgotForm extends AbstractForm
{
	/** @var AccessManager */
	private $accessManager;

	/** @var MailFactory */
	private $messageFactory;

	/** @var UserRepository */
	private $userRepository;


    /**
     * @param AccessManager  $accessManager
     * @param MailFactory  $messageFactory
     * @param UserRepository  $userRepository
     */
    public function __construct(
		AccessManager $accessManager,
		MailFactory $messageFactory,
		UserRepository $userRepository
	) {
		$this->accessManager = $accessManager;
		$this->messageFactory = $messageFactory;
		$this->userRepository = $userRepository;

		$this->setTemplateFile(__DIR__.'/templates/authPasswordForgotForm.latte');
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
     * @param Form  $form
     * @param ArrayHash   $data
     */
    protected function handleSuccess(Form $form, ArrayHash $data)
    {
    	try {
			$user = $this->userRepository->getByEmail($data->email);
			$hash = $this->accessManager->createToken($user, 'Auth:profile', [
				'expire' => '15 minutes',
			]);

			$message = $this->messageFactory->createByType(
				PasswordForgotMessage::class,
				['user' => $user, 'hash' => $hash]
			);

			$message->send();

		} catch (BadRequestException $e) {
			return $form['email']->addError('nette.message.auth-email-unknown');
		}
    }
}
