<?php
/**
 * @author      Haydar KULEKCI <haydarkulekci@gmail.com>
 * @copyright   Copyright (c) Haydar KULEKCI
 * @license     http://mit-license.org/
 *
 * @link        https://github.com/hkulekci/odayonetim-api
 */

namespace ApiComponent;

use ApiComponent\Exception\AccessDeniedException;
use Psr\Http\Message\ServerRequestInterface;

trait AuthInfoTrait
{
    /**
     * @var integer $authenticatedUserId
     */
    protected $authenticatedUserId;
    /**
     * @var integer $authenticatedUserBranchId
     */
    protected $authenticatedUserBranchId;
    /**
     * @var integer $authenticatedUserLegationId
     */
    protected $authenticatedUserLegationId;

    /**
     * @var string $authenticatedClient
     */
    protected $authenticatedClient;

    /**
     * @var array $scopes
     */
    protected $authenticatedScopes = [];

    protected function buildAuthenticateDataFromRequest(ServerRequestInterface $request): void
    {
        $this->setAuthenticatedUserId($request->getAttribute('oauth_user_id'));
        $this->setAuthenticatedUserBranchId($request->getAttribute('oauth_user_branch_id'));
        $this->setAuthenticatedUserLegationId($request->getAttribute('oauth_user_legation_id'));
        $this->setAuthenticatedClient($request->getAttribute('oauth_client_id'));
        $this->setAuthenticatedScopes($request->getAttribute('oauth_scopes') ?? []);
    }

    public function setAuthenticatedUserId($userId): void
    {
        $this->authenticatedUserId = $userId;
    }

    /**
     * @return int
     * @throws AccessDeniedException
     */
    protected function getAuthenticatedUserId(): int
    {
        if (empty($this->authenticatedUserId)) {
            throw new AccessDeniedException('Unacceptable resource usage', 406);
        }

        return $this->authenticatedUserId;
    }

    /**
     * @return int
     */
    public function getAuthenticatedUserBranchId(): ?int
    {
        return $this->authenticatedUserBranchId;
    }

    /**
     * @param int $authenticatedUserBranchId
     */
    public function setAuthenticatedUserBranchId($authenticatedUserBranchId): void
    {
        $this->authenticatedUserBranchId = $authenticatedUserBranchId;
    }

    /**
     * @return int
     */
    public function getAuthenticatedUserLegationId(): ?int
    {
        return $this->authenticatedUserLegationId;
    }

    /**
     * @param int $authenticatedUserLegationId
     */
    public function setAuthenticatedUserLegationId($authenticatedUserLegationId): void
    {
        $this->authenticatedUserLegationId = $authenticatedUserLegationId;
    }

    public function setAuthenticatedClient($client): void
    {
        $this->authenticatedClient = $client;
    }

    protected function getAuthenticatedClient(): ?string
    {
        return $this->authenticatedClient;
    }

    /**
     * @param array $scopes
     */
    public function setAuthenticatedScopes(array $scopes = []): void
    {
        $this->authenticatedScopes = $scopes;
    }

    protected function getAuthenticatedScopes(): array
    {
        return $this->authenticatedScopes;
    }
}
