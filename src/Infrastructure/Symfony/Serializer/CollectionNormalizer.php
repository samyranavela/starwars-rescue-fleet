<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Serializer;

use Illuminate\Support\Collection;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;

final readonly class CollectionNormalizer implements NormalizerInterface, DenormalizerInterface
{
    public function normalize(mixed $data, ?string $format = null, array $context = []): array
    {
        return $data->toArray();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Collection;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Collection
    {
        return new Collection(is_array($data) ? $data : [$data]);
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Collection::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [
            Collection::class => false,
        ];
    }
}
