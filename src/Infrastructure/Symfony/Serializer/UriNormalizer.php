<?php

declare(strict_types=1);

namespace App\Infrastructure\Symfony\Serializer;

use Symfony\Component\Serializer\Exception\NotNormalizableValueException;
use Symfony\Component\Serializer\Normalizer\DenormalizerInterface;
use Symfony\Component\Serializer\Normalizer\NormalizerInterface;
use Uri\Rfc3986\Uri;

final readonly class UriNormalizer implements NormalizerInterface, DenormalizerInterface
{

    public function normalize(mixed $data, ?string $format = null, array $context = []): string
    {
        return $data->toString();
    }

    public function supportsNormalization(mixed $data, ?string $format = null, array $context = []): bool
    {
        return $data instanceof Uri;
    }

    public function denormalize(mixed $data, string $type, ?string $format = null, array $context = []): Uri
    {
        if (!is_string($data)) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                'The data to denormalize must be a string representing a valid URI.',
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
            );
        }

        try {
            // Utilisation de la méthode de fabrique statique native
            return Uri::parse($data);
        } catch (\Throwable $e) {
            throw NotNormalizableValueException::createForUnexpectedDataType(
                sprintf('The string "%s" is not a valid RFC3986 URI: %s', $data, $e->getMessage()),
                $data,
                ['string'],
                $context['deserialization_path'] ?? null,
            );
        }
    }

    public function supportsDenormalization(mixed $data, string $type, ?string $format = null, array $context = []): bool
    {
        return $type === Uri::class;
    }

    public function getSupportedTypes(?string $format): array
    {
        return [Uri::class => true];
    }
}
