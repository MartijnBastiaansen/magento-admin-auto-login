<?php

namespace Onlinepets\AutoLoginAdmin\Http\Controllers;

use Carbon\Carbon;
use Magento\Framework\Webapi\Exception;
use Magento\Store\Model\StoreManagerInterface;
use Magento\User\Model\UserFactory;
use Magento\User\Model\ResourceModel\User as UserResource;
use Onlinepets\AutoLoginAdmin\Http\Requests\CreateRequest;
use Onlinepets\AutoLoginAdmin\Models\UrlModel;

class AutoLoginController
{
    protected $userFactory;
    protected $userResource;
    protected $storeManager;

    public function __construct(StoreManagerInterface $storeManager, UserFactory $userFactory, UserResource $user)
    {
        $this->storeManager = $storeManager;
        $this->userFactory = $userFactory;
        $this->userResource = $user;
    }

    /**
     * @param \Onlinepets\AutoLoginAdmin\Http\Requests\CreateRequest $request
     *
     * @return \Onlinepets\AutoLoginAdmin\Models\UrlModel
     * @throws \Magento\Framework\Exception\NoSuchEntityException
     * @throws \Magento\Framework\Webapi\Exception
     */
    public function create(CreateRequest $request): UrlModel
    {
        $user = $this->userFactory->create()
            ->loadByUsername($request->getUserName());

        if (empty($user->getUserName())) {
            throw new Exception(
                __("User doesn't exist"),
                '400',
                Exception::HTTP_BAD_REQUEST
            );
        }

        if (!$user->getIsActive()) {
            throw new Exception(
                __("User isn't active"),
                '400',
                Exception::HTTP_BAD_REQUEST
            );
        }

        $baseUrl = $this->storeManager->getStore()
            ->getBaseUrl();
        $queryHash = base64_encode(json_encode([
            'user_name' => $user->getUserName(),
            'time' => Carbon::now()
                ->addSeconds(3600)->timestamp,
        ]));

        $url = $baseUrl . 'autologin/autologin/?op-auto-login=' . $queryHash;

        return (new UrlModel())->setUrl($url);
    }
}
