# Proof of Concept: Task (Задача, Задание)

Простая структурная единица, которая позволяет разработчику сформировать Задачу, проверить её статус по запуску, и получить ответ по завершении.
Так же позволяет декомпозировать многосоставные, сложные задачи, на подзадачи, без потери общей связи.

## Глоссарий

- **Task** - контейнер, который содержит описание Задачи, и состояние.
- **TaskStage** - этапы задачи, структурная единица, из которой строится задание. Хранит входные аргументы, и состояние.
- **TaskState** - описывает состояние и Задачи, и Этапа. `getFlag()` для разработчика, `getMessage()` для frontend.
- **TaskFlag** - описывает все возможные состояния в которых может быть Задача, или Этап.

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
