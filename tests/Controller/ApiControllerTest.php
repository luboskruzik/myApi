<?php

namespace App\Tests\Controller;

use App\Controller;
use PHPUnit\Framework\TestCase;
use Symfony\Component\HttpClient\HttpClient;

class ApiControllerTest extends TestCase
{
/*
	public function testGetAllUsers()
	{
		$client = HttpClient::create();
		$response = $client->request(
			'GET',
			'http://localhost/api/users',
			[
				'headers' => [
					'Authorization' => 'Bearer 02791f69e6ac7ce47e9667fddfcb6d0b864c158086019ff6260bfd3bfc68edbe2cdadfffd729c6bc48d11d702eace0a2a977dbe40c7472d805196f33',
					'Accept' => 'application/json'
				],

			]
		);
		var_dump($response->toArray());die();

		$this->assertEquals(1, 1);
	}
*/
	public function testLogin(): string
	{
		$client = HttpClient::create();
		$response = $client->request(
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

		$arr = $response->toArray();
		$this->assertArrayHasKey('token', $arr);

		return $arr['token'];
	}

	/**
	* @depends testLogin
	*/
	public function testSaveUser(string $token): string
	{
		$client = HttpClient::create();
		$response = $client->request(
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
		
		$arr = $response->toArray();
		$this->assertEquals('temp@user.cz', $arr['email']);
		$this->assertEquals(['guest'], $arr['roles']);
		$this->assertEquals('abcd', $arr['password']);
		$this->assertArrayHasKey('id', $arr);

		return $arr['id'];
	}

	/**
	* @depends testSaveUser
	*/
	public function testGetOneUser(string $id): string
	{
		var_dump($id);die();
	}
}