<?php

declare(strict_types=1);

namespace kuaukutsu\poc\task\tools;

use Symfony\Component\Serializer\Encoder\JsonEncoder;
use Symfony\Component\Serializer\Exception\UnsupportedFormatException;
use Symfony\Component\Serializer\Normalizer\ObjectNormalizer;
use Symfony\Component\Serializer\Serializer;

final readonly class SerializerJson
{
    private Serializer $serializer;

    public function __construct()
    {
        $encoders = [new JsonEncoder()];
        $normalizers = [new ObjectNormalizer()];
        $this->serializer = new Serializer($normalizers, $encoders);
    }

    /**
     * @throws UnsupportedFormatException
     */
    public function serialize(mixed $data): string
    {
        return $this->serializer->serialize($data, 'json');
    }

    /**
     * @throws UnsupportedFormatException
     */
    public function deserialize(mixed $data, string $type): mixed
    {
        return $this->serializer->deserialize($data, $type, 'json');
    }
}
