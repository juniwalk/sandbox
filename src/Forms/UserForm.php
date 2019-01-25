<?php declare(strict_types=1);

/**
 * @copyright Design Point, s.r.o. (c) 2016
 * @license   MIT License
 */

namespace App\Forms;

use App\Entity\User;
use App\Entity\UserRepository;
use App\Entity\Enums\Role;
use Carrooi\ImagesManager\ImagesManager;
use Doctrine\ORM\EntityManagerInterface as EntityManager;
use Doctrine\ORM\ORMException;
use Nette\Application\UI\Form;
use Nette\Utils\ArrayHash;
use JuniWalk\Form\AbstractForm;

final class UserForm extends AbstractForm
{
	/** @var EntityManager */
	private $entityManager;

	/** @var ImagesManager */
	private $imagesManager;

	/** @var User */
	private $user;


	/**
	 * @param User|NULL  $user
	 * @param EntityManager  $entityManager
	 * @param ImagesManager  $imagesManager
	 */
	public function __construct(
		?User $user,
		EntityManager $entityManager,
		ImagesManager $imagesManager
	) {
		$this->imagesManager = $imagesManager;
		$this->entityManager = $entityManager;
		$this->user = $user;

		$this->setTemplateFile(__DIR__.'/templates/userForm.latte');
		$this->onBeforeRender[] = function ($form, $template) {
			$template->add('profile', $this->user);
			$this->setDefaults($this->user);
		};
	}


	public function handleRemoveImage(): void
	{
    	$user = $this->getUser();

		if ($user && $image = $this->imagesManager->findImage('avatar', $user)) {
			$this->imagesManager->remove($image);
		}

		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

		} catch(ORMException $e) {
			$form->addError('email', 'nette.message.something-went-wrong');
		}
	}


	/**
	 * @return User|NULL
	 */
	public function getUser(): ?User
	{
		return $this->user;
	}


	/**
	 * @param  User|NULL  $user
	 */
	public function setDefaults(?User $user)
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
     * @param Form  $form
     * @param ArrayHash  $data
     */
    protected function handleSuccess(Form $form, ArrayHash $data)
    {
		if (!$user = $this->getUser()) {
			$user = new User($data->email, $data->name);
		}

		$user->setName($data->name);
		$user->setEmail($data->email);
		$user->setRole($data->role);
		$user->setActive($data->active);

		if ($data->image && $data->image->isOk()) {
			$this->imagesManager->upload($data->image->toImage(), 'avatar', $user);
		}

		try {
			$this->entityManager->persist($user);
			$this->entityManager->flush();

		} catch(UniqueConstraintViolationException $e) {
			return $form['email']->addError('nette.message.auth-email-used');

		} catch(ORMException $e) {
			return $form->addError('nette.message.something-went-wrong');
		}

		$this->user = $user;
    }
}
