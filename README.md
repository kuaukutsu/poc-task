# Proof of Concept: Task (Задача, Задание)

Простая структурная единица, которая позволяет декомпозировать многосоставные, сложные задачи, на подзадачи, без потери
общей связи.
Для запуска задач и контроля за их выполнением используется менеджер процессов основанным на компоненте event-loop.

## Глоссарий

- **Task** - задача, содержит конфигурацию этапов, состояние обработки, и результат работы.
- **TaskStage** - структурная единица задачи, описывает полезное действое в случае успешной, или не успешной работы.
- **TaskManager** - менеджер процессов, контролирует запуск и работу задач.

## Описание работы

Общий процесс можно описать через три основные компоненты:

- задача (**EntityTask**)
- менеджер процессов (**TaskManager**/**pm**)
- обработчик этапов (**EntityHandler**)

Жизненный цикл задачи:

- создание задачи (черновик)
- сохранение задачи и постановка задачи в очередь на выполнение
- запуск задачи
- обработка этапов
- сохранение результата

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
        heartbeat: 5.,
        keeperInterval: 2.,
        handlerEndpoint: 'handler.php',
    )
);
```

- **heartbeat** задаёт частоту проверки заданий для выполнения.
- **keeperInterval** задаёт частоту проверки результатов выполнения.
- **queueSize** задаёт размер очереди заданий на выполнение. Можно представить как количество консумеров для обработки
  заданий.
- **handlerTimeout** задаёт максимальное время для обработки отдельного этапа задачи. По истечении будет выбрашено
  исключение timeout.

#### Обработчик - handlerEndpoint: 'handler.php'

```php

use function kuaukutsu\poc\task\tools\get_previous_uuid;
use function kuaukutsu\poc\task\tools\get_stage_uuid;

$handler = $container->get(StageHandler::class);
$handler->handle(get_stage_uuid(), get_previous_uuid());
```

## Пример

Представим следующую задачу:

- есть файл формата exel на 100_000 строк, и две колонки: ID и NAME
- есть класс обработчик, который на вход получает пару ID и NAME,
  выполняет полезную работу и возвращает результат в виде преобразованной строки
- и есть класс, который получает массив преобразованных строк, и заполняет отчёт (в произвольном порядке)

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
