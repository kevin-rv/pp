<?php

namespace App\Security;

use App\Entity\User;
use Jose\Bundle\JoseFramework\Services\JWEBuilder;
use Jose\Bundle\JoseFramework\Services\JWEBuilderFactory;
use Jose\Bundle\JoseFramework\Services\JWEDecrypter;
use Jose\Bundle\JoseFramework\Services\JWEDecrypterFactory;
use Jose\Component\Core\JWK;
use Jose\Component\Encryption\Serializer\CompactSerializer;
use Jose\Component\KeyManagement\JWKFactory;

class JWT
{
    private const ALGOS_COMP = [
        ['A256GCMKW'],
        ['A256GCM'],
        ['DEF'],
    ];

    /**
     * @var JWK
     */
    private $jwk;
    /**
     * @var JWEBuilder
     */
    private $jweBuilder;
    /**
     * @var JWEDecrypter
     */
    private $jweDecrypter;
    /**
     * @var CompactSerializer
     */
    private $serializer;

    public function __construct(
        string $octKey,
        JWEBuilderFactory $jweBuilderFactory,
        JWEDecrypterFactory $jweDecrypterFactory,
        CompactSerializer $serializer
    ) {
        $this->jwk = new JWK([
            'kty' => 'oct',
            'k' => $octKey,
        ]);

        $this->jweBuilder = $jweBuilderFactory->create(...self::ALGOS_COMP);
        $this->jweDecrypter = $jweDecrypterFactory->create(...self::ALGOS_COMP);
        $this->serializer = $serializer;
    }

    public function generateRandomOctKey(int $size = 2048): string
    {
        $key = JWKFactory::createOctKey($size);

        return $key->get('k');
    }

    public function generateJWT(User $user): string
    {
        $payload = json_encode([
            'iat' => time(),
            'nbf' => time(),
            'exp' => time() + 3600 * 24 * 7,
            'iss' => 'backend',
            'aud' => 'frontend',
            'user_id' => $user->getId(),
            'user_email' => $user->getEmail(),
        ]);

        $jwe = $this->jweBuilder
            ->create()
            ->withPayload($payload)
            ->withSharedProtectedHeader([
                'alg' => self::ALGOS_COMP[0][0],
                'enc' => self::ALGOS_COMP[1][0],
                'zip' => self::ALGOS_COMP[2][0],
            ])
            ->addRecipient($this->jwk)
            ->build()
        ;

        return $this->serializer->serialize($jwe, 0);
    }

    public function decryptToken(string $token)
    {
        $jwe = $this->serializer->unserialize($token);

        if (!$this->jweDecrypter->decryptUsingKey($jwe, $this->jwk, 0)) {
            throw new \InvalidArgumentException('Bad JWT');
        }

        return $jwe;
    }
}
