<?php

namespace App\Tests\Controller;

use App\Controller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class ApiControllerTest extends TestCase
{
	private static $client;

	public static function setUpBeforeClass(): void
	{
		self::$client = HttpClient::create();
	}

	public function testLogin(): string
	{
		$response = self::$client->request(
			'POST',
			'http://localhost/api/login',
			[
				'headers' => [
					'Accept' => 'application/json'
				],
				'json' => [
					'username' => 'test@user.cz',
					'password' => '1234'
				]

			]
		);

		$content = $response->toArray();
		$this->assertArrayHasKey('token', $content);

		return $content['token'];
	}

	/**
	* @depends testLogin
	*/
	public function testSaveUser(string $token): string
	{
		$response = self::$client->request(
			'POST',
			'http://localhost/api/user',
			[
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $token
				],
				'json' => [
					'email' => 'temp@user.cz',
					'roles' => ['guest'],
					'password' => 'abcd'
				]

			]
		);
		
		$content = $response->toArray();
		$this->assertEquals('temp@user.cz', $content['email']);
		$this->assertEquals(['guest'], $content['roles']);
		$this->assertEquals('abcd', $content['password']);
		$headers = $response->getHeaders();

		return $headers['location'][0];
	}

	/**
	* @depends testSaveUser
	* @depends testLogin
	*/
	public function testGetOneUser(string $url, string $token): int
	{
		$response = self::$client->request(
			'GET',
			$url,
			[
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $token
				]
			]
		);
		
		$content = $response->toArray();
		$this->assertEquals('temp@user.cz', $content['email']);
		$this->assertEquals(['guest', 'ROLE_USER'], $content['roles']);

		return $content['id'];
	}

	/**
	* @depends testSaveUser
	* @depends testLogin
	* @depends testGetOneUser
	*/
	public function testUpdateUser(string $url, string $token, int $id): void
	{
		$response = self::$client->request(
			'PUT',
			'http://localhost/api/user',
			[
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $token
				],
				'json' => [
					'id' => $id,
					'email' => 'temp@user.com',
					'roles' => ['admin']
				]

			]
		);

		$content = $response->toArray();
		$this->assertEquals('temp@user.com', $content['email']);
		$this->assertEquals(['admin'], $content['roles']);
		$this->assertEquals($id, $content['id']);
	}

	/**
	* @depends testSaveUser
	* @depends testLogin
	* @depends testGetOneUser
	*/
	public function testDeleteOneUser(string $url, string $token, int $id): void
	{
		$response = self::$client->request(
			'DELETE',
			$url,
			[
				'headers' => [
					'Accept' => 'application/json',
					'Authorization' => 'Bearer ' . $token
				]
			]
		);

		$content = $response->toArray();
		$this->assertEquals('temp@user.com', $content['email']);
		$this->assertEquals(['admin', 'ROLE_USER'], $content['roles']);
		$this->assertEquals($id, $content['id']);
	}

}