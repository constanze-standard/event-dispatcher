# Constanze standard event dispatcher

[![GitHub license](https://img.shields.io/github/license/alienwow/SnowLeopard.svg)](https://github.com/constanze-standard/event-dispatcher/blob/master/LICENSE)
[![Coverage 100%](https://img.shields.io/azure-devops/coverage/swellaby/opensource/25.svg)](https://github.com/constanze-standard/event-dispatcher)

## PSR-14 事件派发与监听系统。
An event dispatching system with PSR-14.

## 简介
event-dispatcher 是一个事件派发系统。它派发一个事件，并以优先级顺序调用预先定义的事件处理程序。

事件系统由以下5个概念构成：
1. 事件 (Event): Event 是事件信息的载体，它往往围绕一个动作进行描述，例如 “用户被创建了”、“准备导出 excel 文件” 等等，Event 的内部需要包含当前事件的所有信息，以便后续的处理程序使用。
2. 监听器 (Listener): Listener 是事件处理程序，负责在发生某一事件(Event)时执行特定的操作。
3. Listener Provider: 它负责将事件(Event)与监听器(Listener)进行关联，在触发一个事件时，Listener Provider 需要提供绑定在该事件上的所有监听器。
4. 派发器 (EventDispatcher): 负责通知某一事件发生了。我们所说的“向某一目标派发一个事件”，这里的“目标”指的是 Listener Provider，也就是说，EventDispatcher 向 Listener Provider 派发了 Event。
5. 订阅器 (Subscriber): 订阅器是 Listener Provider 的扩展，它可以将不同的事件和订阅器里的方法进行自由绑定，这些操作都在订阅器内部进行，这样可以将同类事件的绑定与处理内聚，便于管理。

## 安装
```bash
composer require constanze-standard/event-dispatcher
```

## 使用
### 创建 Event
```php
use ConstanzeStandard\EventDispatcher\Event;

$event = new Event();
echo $event->getName();  // \ConstanzeStandard\EventDispatcher\Event
```
`\ConstanzeStandard\EventDispatcher\Event` 基于 `\ConstanzeStandard\EventDispatcher\Interfaces\EventInterface`，并实现了`StoppableEventInterface`。

`\ConstanzeStandard\EventDispatcher\Interfaces\EventInterface::getName` 方法返回当前事件的唯一标识，默认为类名称。

定制的 `CustomEvent`:
```php
use ConstanzeStandard\EventDispatcher\Event;

class CustomEvent extends Event
{
    private $id;
    private $data;

    public function __construct($id, $data)
    {
        $this->id = $id;
        $this->data = $data;
    }

    public function getName()
    {
        return $id;
    }
}

$event = new CustomEvent('user.create', ['id' => 1, 'name' => 'Alex']);
```

如果你的事件需要在网络间传输，你可能还需要实现 Serializable 接口，以便将有用的信息序列化:
```php
use ConstanzeStandard\EventDispatcher\Event;

class CustomEvent extends Event implements \Serializable
{
    ...

    public function serialize()
    {
        return serialize([
            'name' => $this->name,
            'data' => $this->data
        ]);
    }

    public function unserialize($serialized)
    {
        $array = unserialize($serialized);
        $this->name = $array['name'];
        $this->data = $array['data'];
    }
}

$event = new CustomEvent('user.create', ['id' => 1, 'name' => 'Alex']);
$decompEvent = unserialize(serialize($event));
```

### 定义 Listener
Listener 可以是任意可调用对象，或任意对象的方法，它可以接受一个 `\ConstanzeStandard\EventDispatcher\Interfaces\EventInterface` 的实例作为唯一参数，Listener 必须返回传入的 event 相同类型的实例对象:
```php
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;

// 可调用对象
function onSomeEvent(EventInterface $event) {
    echo $event->getName();
    return $event;
}

class Listener
{
    // 普通的类方法
    public function onSomeEvent(EventInterface $event)
    {
        echo $event->getName();
        return $event;
    }
}
```

`\ConstanzeStandard\EventDispatcher\Event::withPropagationStopped` 将返回一个与当前 event 对象相同，但携带了终止信号的 event，如果将它作为返回值，将终止后续的 event 派发。

```php
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;

function onSomeEvent(EventInterface $event) {
    return $event->withPropagationStopped();
}
```

### 定义 Listener Provider
Listener Provider 负责确定哪些 listener 与当前派发的事件相关，并将相关的 listener 提供给 dispatcher. 
添加一个 Listener:
```php
use ConstanzeStandard\EventDispatcher\ListenerProvider;

$listenerProvider = new ListenerProvider();
$listenerProvider->addListener('user.create', onSomeEvent::class, 2);
$listenerProvider->addListener('user.create', [new Listener(), 'onSomeEvent'], 10);
```
如上所示，通过 `ConstanzeStandard\EventDispatcher\ListenerProvider::addListener` 方法绑定关系，第一个参数是事件 id；第二个参数是监听器对象，可以直接传入可调用对象，或形如 [对象, 方法名称] 的数组；第三个参数是优先级，必须为数字，数字越大优先级越高(越先调用)，默认优先级为 0。

### 事件派发
`ConstanzeStandard\EventDispatcher\EventDispatcher` 在初始化时绑定一个 Listener Provider 实例，然后可以调用 `EventDispatcher::dispatch` 方法将事件发送给 Listener Provider:
```php
use ConstanzeStandard\EventDispatcher\EventDispatcher;
use Psr\EventDispatcher\ListenerProviderInterface;

/** @var ListenerProviderInterface $listenerProvider */
$dispatcher = new EventDispatcher($listenerProvider);
$dispatcher->dispatch($event);
```
上例会直接触发 `$event` 事件，并且按优先级调用 `$listenerProvider` 中绑定到该事件上的所有监听器。

### 使用订阅器
我们之前利用 `ListenerProvider::addListener` 添加事件和监听器的关系，这种方式比较过程化，也无法体现出一组事件之间的关系，所以在实践中往往会提出“订阅器”的概念。

订阅器(Subscriber)实际上是对 `ListenerProvider::addListener` 的一种装饰。使用订阅器需要实现 `ConstanzeStandard\EventDispatcher\Interfaces\SubscriberInterface` 接口:
```php
use ConstanzeStandard\EventDispatcher\Interfaces\EventInterface;
use ConstanzeStandard\EventDispatcher\Interfaces\SubscriberInterface;

class UserSubscriber implements SubscriberInterface
{
    public function subscribe(Closure $subscriber)
    {
        $subscriber(
            'user.signup',
            'onSignup',
            ['onCreate', 1]
        );
    }

    public function onSignup(EventInterface $event)
    {
        ...
        return $event;
    }

    public function onCreate(EventInterface $event)
    {
        ...
        return $event;
    }
}

$listenerProvider->addSubscriber(new UserSubscriber());
```

`ConstanzeStandard\EventDispatcher\Interfaces\SubscriberInterface::subscribe` 方法会提供一个订阅器闭包函数，这个函数的第一个参数是事件 id，后续的参数都是当前类中方法的信息，你可以直接指定方法名称，或用一个数组提供方法名称和优先级。

在 `ConstanzeStandard\EventDispatcher\ListenerProvider::addSubscriber` 的过程中，$subscriber 函数会将当前类中的方法转化为普通的 Listener.
