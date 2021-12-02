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
use Doctrine\ORM\ORMException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;
use Ublaboo\Mailing\Exception\MailingException;

final class AuthSignUpForm extends AbstractForm
{
	/** @var MessageManager */
	private $messageManager;

	/** @var EntityManager */
	private $entityManager;

	/** @var UserManager */
	private $userManager;


	/**
	 * @param UserManager  $userManager
	 * @param EntityManager  $entityManager
     * @param MessageManager  $messageManager
	 */
	public function __construct(
		UserManager $userManager,
		EntityManager $entityManager,
		MessageManager $messageManager
	) {
		$this->messageManager = $messageManager;
		$this->entityManager = $entityManager;
		$this->userManager = $userManager;
	}


	/**
	 * @param  string  $name
	 * @return Form
	 */
	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
        $form->addText('email')->setRequired('web.user.email-required')
            ->addRule($form::EMAIL, 'web.user.email-invalid');
        $form->addPassword('password')->setRequired('web.user.password-required')
            ->addRule($form::MIN_LENGTH, 'web.user.password-length', 6);
		$form->addReCaptcha('recaptcha')->setRequired('web.user.captcha-required');

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
			$user = $this->userManager->createUser(
				$data->email,
				$data->password,
				true
			);

			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$this->messageManager->sendUserSignUpMessage($user);

		} catch(UniqueConstraintViolationException $e) {
			$form['email']->addError('web.message.auth-email-used');

		} catch (MailingException $e) {
			$form->addError('web.message.something-went-wrong');

		} catch (ORMException $e) {
			$form->addError('web.message.something-went-wrong');
		}
    }
}
