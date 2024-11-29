<?php

namespace App\User\Services;

use App\Core\Service;
use App\User\Entities\Otp;
use Carbon\Carbon;

class OtpService extends Service
{
    /**
     * @var object
     */
	private $model;    

	/**
     * @var string
     */
	private $identifier;

	/**
     * @var string
     */
	private $type;

	/**
     * @var number
     */
	private $expirationTime = 5;

    /**
     * Create a otp instance.
     *
     * @return void
     */
	public function __construct()
	{
		$this->model = new Otp();
		$this->expirationTime = config('otp.expiration');
	}

    /**
     * Generate otp code and save in database.
     *
     * @param string $identifier => email address or phone number
     * @param string $identifierType => ['phone_number', 'email']
     * @param string $type => ['register', 'login', 'change_password', 'reset_password']
     * @return string
     */
	public function generate(string $identifier, string $identifierType = 'phone_number', string $type = 'register')
	{
		$this->identifier = $identifier;
		$this->type = $type;

		if($userOtp = $this->hasOtpCode(['identifier' => $this->identifier,'type' => $this->type])) {
			$expireAt = Carbon::parse($userOtp->created_at)->addMinutes($this->expirationTime);
			if(now()->isBefore($expireAt)) {
				return +$userOtp->token;
			}
			$userOtp->delete();
		}

		$otpCode = Otp::create([
			'identifier'     => $identifier,
			'identifierType' => $identifierType,
			'token'          => $this->randomCodeGenerator(),
			'type'           => $type,
		]);

		return $otpCode->token;
	}

    /**
     * Validate opt code
     *
     * @param string $identifier => email address or phone number
     * @param string $type => ['register', 'login', 'change_password', 'reset_password']
     * @return boolean
     */
	public function validate(string $identifier, string $type, string $token)
	{
		$this->identifier = $identifier;
		$this->type = $type;

		if($userOtp = $this->hasOtpCode(['identifier' => $this->identifier,'type' => $this->type, 'token' => $token])) {
			$expireAt = Carbon::parse($userOtp->created_at)->addMinutes($this->expirationTime);
			$userOtp->delete();
			if(now()->isBefore($expireAt)) {
				return true;
			}
		}

		return false;
	}

    /**
     * There is an active otp corresponding to our desired ID or not
     *
     * @param $condition
     * @return object
     */
	private function hasOtpCode($condition)
	{
		return $this->model->where($condition)->first();
	}

    /**
     * Get random code.
     *
     * @return string
     */
	private function randomCodeGenerator()
	{
		$randomCode = $this->randomCode();
		if($this->isUnique($randomCode)) {
			return $randomCode;
		}
		return $this->randomCodeGenerator();
	}

    /**
     * Get random code.
     *
     * @return string
     */
	private function randomCode()
	{
		return rand(123456, 999999);
	}

    /**
     * Check that the same code does not already exist
     *
     * @param string $token
     * @return boolean
     */
	private function isUnique(string $token)
	{
		return ! $this->model->where('token', $token)->exists();
	}
}