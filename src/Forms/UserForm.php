<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2020
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use App\Entity\Enums\Role;
use Contributte\ImageStorage\ImageStorage;
use Doctrine\DBAL\Exception\UniqueConstraintViolationException;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;

final class UserForm extends AbstractForm
{
	/** @var EntityManager */
	private $entityManager;

	/** @var ImageStorage */
	private $imageStorage;

	/** @var User */
	private $user;


	/**
	 * @param User|null  $user
	 * @param ImageStorage  $imageStorage
	 * @param EntityManager  $entityManager
	 */
	public function __construct(
		?User $user,
		ImageStorage $imageStorage,
		EntityManager $entityManager
	) {
		$this->entityManager = $entityManager;
		$this->imageStorage = $imageStorage;
		$this->user = $user;

		$this->setTemplateFile(__DIR__.'/templates/userForm.latte');
		$this->onBeforeRender[] = function ($form, $template) {
			$template->add('imageStorage', $this->imageStorage);
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

		if (!$image = $user->getImage()) {
			return;
		}

		try {
			$this->imageStorage->delete($image);
			$user->setImage(null);

			$this->entityManager->flush();

		} catch(UniqueConstraintViolationException $e) {
			$form->addError('email', 'nette.message.email-taken');
		}

		$this->redrawControl('form');
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
			'role' => $user->getRole(),
			'active' => $user->isActive(),
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
		$form->addSelect('role')->setItems((new Role)->getItems());
		$form->addText('email')->setRequired('nette.user.email-required')->setType('email');
		$form->addCheckbox('active');

        $form->addSubmit('submit');
        $form->addSubmit('apply');

		return $form;
	}


    /**
     * @param  Form  $form
     * @param  ArrayHash  $data
	 * @return void
     */
    protected function handleSuccess(Form $form, ArrayHash $data): void
    {
		if (!$user = $this->getUser()) {
			$user = new User($data->email, $data->name);
		}

		$user->setName($data->name);
		$user->setEmail($data->email);
		$user->setRole($data->role);
		$user->setActive($data->active);

		if ($data->image->isOk()) {
			$image = $this->imageStorage->saveUpload($data->image, 'avatar');
			$user->setImage($image);
		}

		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();
			$this->user = $user;

		} catch(UniqueConstraintViolationException $e) {
			$form['email']->addError('nette.message.auth-email-used');

		} catch(ORMException $e) {
			$form->addError('nette.message.something-went-wrong');
		}
    }
}
