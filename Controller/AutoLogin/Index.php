<?php

namespace Onlinepets\AutoLoginAdmin\Controller\AutoLogin;

use Carbon\Carbon;
use Magento\Framework\App\Action\Action;
use Magento\Framework\App\Action\Context;
use Magento\Framework\App\Request\Http as RequestHttp;
use Magento\Framework\Exception\NotFoundException;
use Magento\Framework\ObjectManager\ConfigLoaderInterface;
use Magento\Framework\ObjectManagerInterface;
use Magento\User\Model\UserFactory;

class Index extends Action
{
    protected $userFactory;
    protected $objectManager;
    protected $request;
    protected $configLoader;

    public function __construct(
        Context $context,
        UserFactory $userFactory,
        ObjectManagerInterface $objectManager,
        RequestHttp $request,
        ConfigLoaderInterface $configLoader
    ) {
        $this->userFactory = $userFactory;
        $this->objectManager = $objectManager;
        $this->request = $request;
        $this->configLoader = $configLoader;
        parent::__construct($context);
    }

    public function execute()
    {
        $autoLogin = json_decode(base64_decode($this->getRequest()
            ->getParam('op-auto-login')), false);

        if (!is_object($autoLogin) ||
            !isset($autoLogin->time, $autoLogin->user_name) ||
            $autoLogin->time <= Carbon::now()->timestamp
        ) {
            throw new NotFoundException(__('Page not found.'));
        }

        $areaCode = 'adminhtml';
        $username = $autoLogin->user_name;

        $this->request->setPathInfo('/admin');
        $this->objectManager->configure($this->configLoader->load($areaCode));

        /** @var \Magento\User\Model\User $user */
        $user = $this->objectManager->get('Magento\User\Model\User')
            ->loadByUsername($username);

        /** @var \Magento\Backend\Model\Auth\Session $session */
        $session = $this->objectManager->get('Magento\Backend\Model\Auth\Session');
        $session->setUser($user);
        $session->processLogin();

        if ($session->isLoggedIn()) {
            $cookieManager = $this->objectManager->get('Magento\Framework\Stdlib\CookieManagerInterface');
            $cookieValue = $session->getSessionId();
            if ($cookieValue) {
                $sessionConfig = $this->objectManager->get('Magento\Backend\Model\Session\AdminConfig');
                $cookiePath = str_replace('autologin.php', 'index.php', $sessionConfig->getCookiePath());
                $cookieMetadata = $this->objectManager->get('Magento\Framework\Stdlib\Cookie\CookieMetadataFactory')
                    ->createPublicCookieMetadata()
                    ->setDuration(3600)
                    ->setPath($cookiePath)
                    ->setDomain($sessionConfig->getCookieDomain())
                    ->setSecure($sessionConfig->getCookieSecure())
                    ->setHttpOnly($sessionConfig->getCookieHttpOnly());
                $cookieManager->setPublicCookie($sessionConfig->getName(), $cookieValue, $cookieMetadata);

                if (class_exists('Magento\Security\Model\AdminSessionsManager')) {
                    /** @var \Magento\Security\Model\AdminSessionsManager $adminSessionManager */
                    $adminSessionManager = $this->objectManager->get('Magento\Security\Model\AdminSessionsManager');
                    $adminSessionManager->processLogin();
                }
            }

            /** @var \Magento\Backend\Model\UrlInterface $backendUrl */
            $backendUrl = $this->objectManager->get('Magento\Backend\Model\UrlInterface');
            $path = $backendUrl->getStartupPageUrl();
            $url = $backendUrl->getUrl($path);
            $url = str_replace('autologin.php', 'index.php', $url);
            header('Location:  ' . $url);
            exit;
        }

        return $this->_response;
    }
}
