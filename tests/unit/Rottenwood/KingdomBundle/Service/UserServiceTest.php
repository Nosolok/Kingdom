<?php
/**
 * Author: Rottenwood
 * Date Created: 05.11.15 23:11
 */

namespace Rottenwood\KingdomBundle\Service;

use Monolog\Logger;
use Rottenwood\KingdomBundle\Entity\Infrastructure\InventoryItemRepository;
use Rottenwood\KingdomBundle\Entity\Infrastructure\RoomRepository;
use Rottenwood\KingdomBundle\Entity\Infrastructure\UserRepository;
use Rottenwood\KingdomBundle\Entity\Room;
use Snc\RedisBundle\Client\Phpredis\Client;
use Symfony\Component\HttpKernel\KernelInterface;

class UserServiceTest extends \PHPUnit_Framework_TestCase
{

    private $kernel;
    private $redis;
    private $logger;
    private $userRepository;
    private $inventoryItemRepository;
    private $roomRepository;

    protected function setUp()
    {
        $this->kernel = \Phake::mock(KernelInterface::class);
        $this->redis = \Phake::mock(Client::class);
        $this->logger = \Phake::mock(Logger::class);
        $this->userRepository = \Phake::mock(UserRepository::class);
        $this->inventoryItemRepository = \Phake::mock(InventoryItemRepository::class);
        $this->roomRepository = \Phake::mock(RoomRepository::class);
    }

    /** @test */
    public function transliterate_GivenEnglishName_ReturnsRussianTranslation()
    {
        $userService = $this->createUserService();

        foreach ($this->getNames() as $englishName => $russianName) {
            $this->assertEquals($userService->transliterate($englishName), $russianName);
        }
    }

    /** @test */
    public function getStartRoom_GivenRoomRepositoryWithDefaultRoom_ReturnsDefaultRoom()
    {
        $this->givenRoomRepositoryWithDefaultRoom();

        $userService = $this->createUserService();

        $this->assertInstanceOf(Room::class, $userService->getStartRoom());
    }

    /**
     * @return UserService
     */
    private function createUserService()
    {
        return new UserService($this->kernel,
            $this->redis,
            $this->logger,
            $this->userRepository,
            $this->inventoryItemRepository,
            $this->roomRepository
        );
    }

    /**
     * @return array
     */
    public function getNames()
    {
        return [
            'Tester' => 'Тестер',
            'Paul'   => 'Паул',
            'Ringo'  => 'Райнго',
            'John'   => 'Джохн',
            'George' => 'Георге',
        ];
    }

    private function givenRoomRepositoryWithDefaultRoom()
    {
        \Phake::when($this->roomRepository)
            ->findOneByXandY(\Phake::anyParameters())
            ->thenReturn(\Phake::mock(Room::class));
    }
}