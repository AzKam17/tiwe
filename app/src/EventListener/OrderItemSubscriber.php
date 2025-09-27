<?php

namespace App\EventListener;

use App\Entity\OrderItem;
use App\Entity\User;
use Doctrine\Bundle\DoctrineBundle\Attribute\AsEntityListener;
use Doctrine\Common\EventSubscriber;
use Doctrine\ORM\EntityManagerInterface;
use Doctrine\ORM\Event\PostUpdateEventArgs;
use Doctrine\ORM\Events;
use Doctrine\Persistence\Event\LifecycleEventArgs;

#[AsEntityListener(event: Events::postUpdate, method: 'postUpdate', entity: OrderItem::class)]
class OrderItemSubscriber
{
    public function __construct(
        protected readonly EntityManagerInterface $em
    )
    {}

    public function postUpdate(OrderItem $entity, PostUpdateEventArgs $event): void
    {
        dump('1');
        $order = $entity->getMaster();
        if (!$order) {
            dump('1q');
            return;
        }

        dump('2');
        $order->updateStatusBasedOnItems();
        dump($order);
        $this->em->persist($order);
        $this->em->flush();
    }
}
