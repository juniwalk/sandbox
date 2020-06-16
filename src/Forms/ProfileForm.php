<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;

final class ProfileForm extends AbstractForm
{
	/** @var EntityManager */
	private $entityManager;

	/** @var User */
	private $user;


	/**
	 * @param User  $user
	 * @param EntityManager  $entityManager
	 */
	public function __construct(
		User $user,
		EntityManager $entityManager
	) {
		$this->entityManager = $entityManager;
		$this->user = $user;

		$this->setTemplateFile(__DIR__.'/templates/profileForm.latte');
		$this->onBeforeRender[] = function ($form, $template) {
			$template->add('profile', $this->user);
			$this->setDefaults($this->user);
		};
	}


	/**
	 * @return void
	 */
	public function handleRemoveImage(): void
	{
    	$user = $this->getUser();

		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

		} catch(UniqueConstraintViolationException $e) {
			$form->addError('email', 'nette.message.email-taken');
		}
	}


	/**
	 * @return User|null
	 */
	public function getUser(): ?User
	{
		return $this->user;
	}


	/**
	 * @param  User|null  $user
	 * @return void
	 */
	public function setDefaults(?User $user): void
	{
		$form = $this->getForm();

		if (!isset($user)) {
			return;
		}

		$form->setDefaults([
			'name' => $user->getName(),
			'email' => $user->getEmail(),
		]);
	}


	/**
	 * @param  string  $name
	 * @return Form
	 */
	protected function createComponentForm(string $name): Form
	{
		$form = parent::createComponentForm($name);
		$form->addUpload('image')->addCondition($form::FILLED)
			->addRule($form::MAX_FILE_SIZE, 'nette.user.image-too-large', 2097152)
			->addRule($form::IMAGE, 'nette.user.image-invalid');
		$form->addText('name')->setRequired('nette.user.name-required');
		$form->addText('email')->setRequired('nette.user.email-required')->setType('email');
		$form->addPassword('password')->addCondition($form::FILLED)
			->addRule($form::MIN_LENGTH, 'nette.user.password-length', 6);

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
    	$user = $this->getUser();
		$user->setName($data->name);
		$user->setEmail($data->email);

		if (!empty($data->password)) {
			$user->setPassword($data->password);
		}

		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

		} catch(UniqueConstraintViolationException $e) {
			$form->addError('email', 'nette.message.email-taken');
		}
    }
}
