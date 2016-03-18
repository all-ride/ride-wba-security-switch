<?php

namespace ride\web\security\user\controller;

use ride\library\security\exception\UserNotFoundException;
use ride\library\security\exception\UnauthorizedException;
use ride\library\security\exception\UserSwitchException;
use ride\library\validation\exception\ValidationException;
use ride\library\validation\ValidationError;
use ride\web\base\controller\AbstractController;
use ride\web\mvc\view\JsonView;
use ride\library\security\SecurityManager;

class UserSwitchController extends AbstractController {

    /**
     * Name of the auto complete action for the username
     * @var string
     */
    const ACTION_AUTO_COMPLETE_USER = 'autocomplete';

    /**
     * Translation key for the title of this module
     * @var string
     */
    const TRANSLATION_TITLE = 'user.title.switch';

    /**
     * Translation key for the user not found error
     * @var string
     */
    const TRANSLATION_ERROR_USER_NOT_FOUND = 'user.error.not.found';

    /**
     * Translation key for the user not allowed error
     * @var string
     */
    const TRANSLATION_ERROR_USER_NOT_ALLOWED = 'user.error.not.allowed';

    /**
     * Property key for the username
     * @var unknown_type
     */
    const PROPERTY_USERNAME = 'username';

    /**
     * Instance of the security manager
     * @var \ride\library\security\SecurityManager
     */
    private $securityManager;

    public function __construct(SecurityManager $securityManager) {
        $this->securityManager = $securityManager;
    }

    public function indexAction($username = null) {
        $translator = $this->getTranslator();

        if ($username) {
            $this->securityManager->switchUser($username);
            $this->response->setRedirect($this->getUrl('admin'));
            return;
        }

        $form = $this->createFormBuilder();
        $form->addRow(self::PROPERTY_USERNAME, 'select', array (
            'label' => $translator->translate('label.username'),
            'options' => $this->autocompleteAction(),
            'multiple' => false,
            'validators' => array(
                'required' => array(),
            )
        ));

        $form = $form->build();

        if ($form->isSubmitted()) {
            try {
                $form->validate();

                $data = $form->getData();
                $username = $data['username'];

                $this->securityManager->switchUser($username);
                $this->response->setRedirect($this->getUrl('admin'));
                return;

            } catch (UserNotFoundException $userNotFoundException) {
                $validationError = new ValidationError(self::TRANSLATION_ERROR_USER_NOT_FOUND, 'Could not find user %user%', array('user' => $username));

                $validationException = new ValidationException();
                $validationException->addErrors(self::PROPERTY_USERNAME, array($validationError));
                $form->setValidationException($validationException);

            } catch (UserSwitchException $exception) {
                $validationError = new ValidationError(self::TRANSLATION_ERROR_USER_NOT_ALLOWED, 'You are not allowed to switch to %user%', array('user' => $username));

                $validationException = new ValidationException();
                $validationException->addErrors(self::PROPERTY_USERNAME, array($validationError));
                $form->setValidationException($validationException);

            } catch (ValidationException $exception) {
                $this->setValidationException($exception, $form);
            }
        }

        $view = $this->setTemplateView('base/user.switch', array(
            'form' => $form->getView(),
            'referer' => $this->request->getUrl(),
        ));

        $form->processView($view);
    }

    /**
     * Action to auto complete a username
     * @return null
     */
    public function autocompleteAction() {
        $securityModel = $this->securityManager->getSecurityModel();
        $users = $securityModel->getUsers();
        $userArray= array();
        foreach ($users as $index => $user) {
            $roles = null;
            $counter = 1;
            foreach ($user->getRoles() as $role) {
                if ($counter !== 1) {
                    $roles .= ', ';
                }
                $roles .= $role->getName();
                $counter++;
            }
            $userArray[$user->getUsername()] = $user->getUsername() . ' (' . $roles  .  ')';
        }
        return $userArray;

        //$view = new JsonView($users);
        //$this->response->setView($view);
    }
}
