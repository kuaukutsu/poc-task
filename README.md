# Proof of Concept: Task (Задача, Задание)

Простая структурная единица, которая позволяет декомпозировать многосоставные, сложные задачи, на подзадачи, без потери
общей связи. Для запуска задач и контроля за их выполнением используется менеджер процессов (_TaskManager_) основанный на компоненте event-loop.

## Глоссарий

- **Node** - узел, возможность развести по отдельным сервисам различные обрабатываемые кейсы.
- **Task** - задача, содержит конфигурацию этапов - подзадач, состояние обработки, и результат работы.
- **TaskStage** - структурная единица задачи - этап, подзадача. Описывает полезную работу.
- **TaskManager** - менеджер процессов, контролирует запуск и работу задач.

## Описание работы

Общий процесс можно описать через три основные компоненты:

- узел (**EntityNode**)
- задача (**EntityTask**)
- менеджер процессов (**TaskManager**)
- обработчик этапов (**TaskHandler**)

Жизненный цикл задачи:

- создание задачи через черновик (application)
- сохранение задачи и постановка задачи в очередь на выполнение (application)
- запуск задачи (task manager)
- обработка этапов (task manager/task handler)
- сохранение результата (task manager/task handler)

## Пример

Например, 
- у нас есть базовая задача загрузить файл-табличку на 100_000 строк, 
- выполнить над каждой строкой какую-то полезную работу, 
- и после того как все записи будут обработаны финализировать - выполнить какую-то дополнительную полезную работу.

В первом приближении можно сказать что задача решается с помощью очереди, но это не совсем так, 
поскольку не создавая дополнительный обвес (хранение метаданных), мы не получим:
- точку во времени когда выполнились все задачи
- состония каждой отдельной задачи

Опять же, данное решение будет удобно если нам нужно сделать пре и пост обработку данных.

В парадигме task, решение выглядит следующим образом:
- создаём задачу (task) состоящую из двух этапов (task stage)
- 1 этап: 
  - распарсить файл, 
  - получить массив строк, 
  - определить общий контекст для всех строк файла,
  - на основе массива данных собрать вложенную задачу, где каждый этап это либо 1 строка из файла, либо массив (batch) строк
- 1/2 этап: после выполнения первого этапа, будет создана вложенная задача, где каждый этап (строка или batch) будет выполняться ассинхронно, в отдельном php процессе.
- 2 этап: после того как будет выполнена обработка (1/2 этап) всех строк, выполнение будет передано в данный этап. 
Здесь мы можем сделать анализ того какие данные были загружены успешно, были ли ошибки, и выполнить какую-то полезную работу.

## Дисклаймер

Данное решение - библиотека, и не описывает то, как вы будете хранить состояние системы. 
Для этого необходимо реализовать интерфейсы:
- \kuaukutsu\poc\task\service\TaskQuery
- \kuaukutsu\poc\task\service\TaskCommand
- \kuaukutsu\poc\task\service\StageQuery
- \kuaukutsu\poc\task\service\StageCommand

В этом репозитории - https://github.com/kuaukutsu/yii2-component-demo можно посмотреть примерно того, 
как это может быть реализовано в рамках [Yii2](https://github.com/yiisoft/yii2) framework.

Данное решение подойдёт для небольших проектов, и/или в качестве прототипирования решения.
Если необходимо взрослое решение, то лучше сразу смотреть в сторону [temporal.io](https://github.com/temporalio/temporal)

### Создание задачи

```php
$builder = $container->get(TaskBuilder::class);

/**
 * Создание черновика задачи
 * @var TaskDraft $draft
 */
$draft = $builder->create(
    'Count Numbers',
    new EntityWrapper(
        class: IncreaseNumberStageStub::class,
        params: [
            'name' => 'Number initialization.',
        ],
    ),
);

/**
 * Записываем задачу и ставим её в очередь на выполнение
 * @var EntityTask $task
 */
$task = $builder->build($draft);
```

### Process Manager

```php
$manager = $container->get(TaskManager::class);
$manager->run(
    new TaskManagerOptions(
        bindir: __DIR__,
        heartbeat: (float)argument('heartbeat', 2),
        keeperInterval: (float)argument('iterval', 1),
        queueSize: (int)argument('process', 30),
        handler: 'handler.php',
    )
);
```

- **heartbeat** частота постановки задач в очередь для выполнения.
- **keeperInterval** частота проверки результатов выполнения запущенных процессов.
- **queueSize** размер очереди задач. Условно можно представить как количество консумеров для обработки задач.
- **timeout** задаёт максимальное время для обработки отдельного этапа задачи. По истечении будет выбрашено исключение timeout.
- **handler** программа обработки задач. Любая задача это отдельный php процесс - обработчик для выполнения полезной работы.

#### Обработчик - handler: 'handler.php'

```php

use function kuaukutsu\poc\task\tools\get_previous_uuid;
use function kuaukutsu\poc\task\tools\get_stage_uuid;
use function kuaukutsu\poc\task\tools\get_task_uuid;

$handler = $container->get(StageHandler::class);
$exitCode = $handler->handle(
    get_task_uuid(),
    get_stage_uuid(),
    get_previous_uuid(),
);
exit($exitCode);
```

## Docker

```shell
docker pull ghcr.io/kuaukutsu/php:8.1-cli
```

Container:
- `ghcr.io/kuaukutsu/php:${PHP_VERSION}-cli` (**default**)
- `jakzal/phpqa:php${PHP_VERSION}`

shell

```shell
docker run --init -it --rm -v "$(pwd):/app" -w /app ghcr.io/kuaukutsu/php:8.1-cli sh
```

## Testing

### Run

Самый лучший пример - это рабочий код.

В данном случае имеем две ручки:
- **builder** - постановка задач
- **runner** - обработка задач

**builder**

```shell
make test-builder
```

**runner**

```shell
make test-pm
```

### Unit testing

The package is tested with [PHPUnit](https://phpunit.de/). To run tests:

```shell
make phpunit
```

### Static analysis

The code is statically analyzed with [Psalm](https://psalm.dev/). To run static analysis:

```shell
make psalm
```

### Code Sniffer

```shell
make phpcs
```

### Rector

```shell
make rector
```
