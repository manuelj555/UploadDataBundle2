<?php
/**
 * 26/09/14
 * upload
 */

namespace Manuel\Bundle\UploadDataBundle\Profiler;

use Symfony\Component\EventDispatcher\EventDispatcherInterface;
use Symfony\Component\HttpKernel\DataCollector\ExceptionDataCollector;
use Symfony\Component\HttpKernel\Event\FilterResponseEvent;
use Symfony\Component\HttpKernel\Kernel;
use Symfony\Component\HttpKernel\KernelEvents;
use Symfony\Component\HttpKernel\Profiler\Profiler;

class ExceptionProfiler
{
    /**
     * @var EventDispatcherInterface
     */
    private $eventDispatcher;
    /**
     * @var Profiler|null
     */
    private $profiler;

    /**
     * ExceptionProfiler constructor.
     *
     * @param EventDispatcherInterface $eventDispatcher
     * @param null|Profiler $profiler
     */
    public function __construct(EventDispatcherInterface $eventDispatcher, Profiler $profiler = null)
    {
        $this->eventDispatcher = $eventDispatcher;
        $this->profiler = $profiler;
    }

    public function addException(\Exception $exception)
    {
        if (null === $this->profiler) {
            return;
        }

        if (!$this->profiler->has('exception')) {
            return;
        }

        $this->eventDispatcher->addListener(
            KernelEvents::RESPONSE,
            function (FilterResponseEvent $event) use ($exception) {
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