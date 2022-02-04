<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Profiler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\Event\ResponseEvent;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;
use Throwable;

class ExceptionProfiler
{
    public function __construct(
        private EventDispatcherInterface $eventDispatcher,
        private ?Profiler $profiler = null,
    ) {
    }

    public function addException(Throwable $exception)
    {
        if (null === $this->profiler) {
            return;
        }

        if (!$this->profiler->has('exception')) {
            return;
        }

        $this->eventDispatcher->addListener(
            KernelEvents::RESPONSE,
            function (ResponseEvent $event) use ($exception) {
                if (!$event->isMasterRequest()) {
                    return;
                }

                $this->profiler
                    ->get('exception')
                    ->collect($event->getRequest(), $event->getResponse(), $exception);
            },
            1000
        );
    }
}