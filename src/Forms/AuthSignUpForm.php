<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use App\Messages\UserSignUpMessage;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;
use Ublaboo\Mailing\MailFactory;

final class AuthSignUpForm extends AbstractForm
{
	/** @var MailFactory */
	private $messageFactory;

	/** @var EntityManager */
	private $entityManager;


	/**
     * @param MailFactory  $messageFactory
	 * @param EntityManager  $entityManager
	 */
	public function __construct(
		MailFactory $messageFactory,
		EntityManager $entityManager
	) {
		$this->messageFactory = $messageFactory;
		$this->entityManager = $entityManager;

		$this->setTemplateFile(__DIR__.'/templates/authSignUpForm.latte');
	}


	/**
	 * @param  string  $name
	 * @return Form
	 */
	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addText('name')->setRequired('nette.user.name-required');
        $form->addText('email')->setRequired('nette.user.email-required')
            ->addRule($form::EMAIL, 'nette.user.email-invalid');
        $form->addPassword('password')->setRequired('nette.user.password-required')
            ->addRule($form::MIN_LENGTH, 'nette.user.password-length', 6);
        $form->addCheckbox('agreement');
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
    	$user = new User($data->email, $data->name);
		$user->setPassword($data->password);

		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

			$message = $this->messageFactory->createByType(
				UserSignUpMessage::class,
				['user' => $user]
			);

			$message->send();

		} catch(UniqueConstraintViolationException $e) {
			$form['email']->addError('nette.message.auth-email-used');

		} catch (ORMException $e) {
			$form->addError('nette.message.something-went-wrong');
		}
    }
}
