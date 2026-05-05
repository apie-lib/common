<?php
namespace Apie\Common\Other;

use Apie\Core\Context\ApieContext;
use Apie\Core\ValueObjects\CompositeValueObject;
use Apie\Core\ValueObjects\Interfaces\ValueObjectInterface;
use Psr\Http\Message\ServerRequestInterface;
use RKA\Middleware\IpAddress;
use Symfony\Component\HttpFoundation\Request;

class AuditOrigin implements ValueObjectInterface
{
    use CompositeValueObject;

    public function __construct(
        private readonly ?string $clientIp = null,
        private readonly ?string $clientUserAgent = null,
        private readonly ?string $serverName = null,
        private readonly ?string $terminalUserName = null,
    ) {
    }

    public function getClientIp(): ?string
    {
        return $this->clientIp;
    }

    public function getClientUserAgent(): ?string
    {
        return $this->clientUserAgent;
    }

    public function getServerName(): ?string
    {
        return $this->serverName;
    }

    public function getTerminalUserName(): ?string
    {
        return $this->terminalUserName;
    }

    public static function createFromContext(ApieContext $context): self
    {
        $clientIp = null;
        $clientUserAgent = null;
        $serverName = null;
        $terminalUserName = null;

        $request = $context->getContext(ServerRequestInterface::class, false);
    
        if ($request instanceof ServerRequestInterface) {
            $trustedProxies = ['127.0.0.1'];
            if (class_exists(Request::class)) {
                $trustedProxies = Request::getTrustedProxies();
            }
            $ip = new class(checkProxyHeaders: true, trustedProxies: $trustedProxies, headersToInspect: [ 'CF-Connecting-IP', 'True-Client-IP', 'X-Real-IP', 'Forwarded', 'X-Forwarded-For', 'X-Forwarded', 'X-Cluster-Client-Ip', 'Client-Ip', ]) extends IpAddress {
                public function determineClientIpAddress($request): ?string
                {
                    return parent::determineClientIpAddress($request);
                }
            };
            $clientIp = $ip->determineClientIpAddress($request);
            $clientUserAgent = $request->getHeaderLine('User-Agent') ? : null;
            ;
        } else {
            $serverName = gethostname();
            $terminalUserName = get_current_user();
        }
        return new self(
            $clientIp,
            $clientUserAgent,
            $serverName,
            $terminalUserName
        );
    }
}
