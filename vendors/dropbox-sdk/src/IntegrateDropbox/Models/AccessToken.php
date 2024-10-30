<?php
namespace CodeConfig\IntegrateDropbox\SDK\Models;

class AccessToken extends BaseModel
{
    /**
     * Access Token
     *
     * @var string
     */
    protected $token;


    /**
     * Refresh Token
     *
     * @var string
     */
    protected $refreshToken;

    /**
     * Expiry Time for the token
     *
     * @var string
     */
    protected $expiryTime;

    /**
     * Token Type
     *
     * @var string
     */
    protected $tokenType;

    /**
     * Bearer
     *
     * @var string
     */
    protected $bearer;

    /**
     * User ID
     *
     * @var string
     */
    protected $uid;

    /**
     * Account ID
     *
     * @var string
     */
    protected $accountId;

    /**
     * Team ID
     *
     * @var string
     */
    protected $teamId;

    /**
     * Created.
     *
     * @var string
     */
    protected $created;

    /**
     * Create a new AccessToken instance
     *
     * @param array $data
     */
    public function __construct(array $data)
    {
        parent::__construct($data);

        $this->token = $this->getDataProperty('access_token');
        $this->tokenType = $this->getDataProperty('token_type');
        $this->bearer = $this->getDataProperty('bearer');
        $this->uid = $this->getDataProperty('uid');
        $this->accountId = $this->getDataProperty('account_id');
        $this->teamId = $this->getDataProperty('team_id');
        $this->expiryTime = $this->getDataProperty('expires_in');
        $this->refreshToken = $this->getDataProperty('refresh_token');
        $this->created = $this->getDataProperty('created');
    }

    /**
     * Get Access Token
     *
     * @return string
     */
    public function getToken()
    {

        return $this->token;
    }

    /**
     * Set Access Token
     *
     * @return string
     */
    public function setToken($token)
    {
        $this->token = $token;

        return $this;
    }

    /**
     * Get Refresh Token
     *
     * @return string
     */
    public function getRefreshToken()
    {
        return $this->refreshToken;
    }

    /**
     * Get Refresh Token
     *
     * @return string
     */
    public function setRefreshToken($refreshToken)
    {
        $this->refreshToken = $refreshToken;
        return $this;
    }

    /**
     * Get the expiry time
     *
     * @return int
     */
    public function getExpiryTime()
    {
        return (int) $this->expiryTime;
    }

    /**
     * Set the expiry time
     *
     * @return int
     */
    public function setExpiryTime($expiryTime)
    {
        $this->expiryTime = $expiryTime;

        return $this;
    }

    /**
     * Get Token Type
     *
     * @return string
     */
    public function getTokenType()
    {
        return $this->tokenType;
    }

    /**
     * Get Token Type
     *
     * @return string
     */
    public function setTokenType($tokenType)
    {
        $this->tokenType = $tokenType;

        return $this;
    }

    /**
     * Get Bearer
     *
     * @return string
     */
    public function getBearer()
    {
        return $this->bearer;
    }

    /**
     * Get User ID
     *
     * @return string
     */
    public function getUid()
    {
        return $this->uid;
    }

    /**
     * Get Account ID
     *
     * @return string
     */
    public function getAccountId()
    {
        return $this->accountId;
    }

    /**
     * Get Team ID
     *
     * @return string
     */
    public function getTeamId()
    {
        return $this->teamId;
    }

    /**
     * Set created.
     *
     * @param string $created created
     *
     * @return self
     */
    public function setCreated(string $created)
    {
        $this->created = $created;

        return $this;
    }
    /**
     * Get created.
     *
     * @return string
     */
    public function getCreated()
    {

        return $this->created;
    }

}
