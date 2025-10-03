<?php

namespace MageOS\PageBuilderTemplateImportExport\Service\Dropbox;

use GuzzleHttp\Exception\ClientException;

class Client extends \Spatie\Dropbox\Client
{

    protected function getEndpointUrl(string $subdomain, string $endpoint): string
    {
        if (count($parts = explode('::', $endpoint)) === 2) {
            [$subdomain, $endpoint] = $parts;
        }

        if ($endpoint === "oauth2/token") {
            return "https://{$subdomain}.dropbox.com/{$endpoint}";
        }

        return "https://{$subdomain}.dropboxapi.com/2/{$endpoint}";
    }

    /**
     * @param  array<string, string|bool|array<string>>|null  $parameters
     * @return array<mixed>
     */
    public function apiEndpointRequest(string $endpoint, ?array $parameters = null): array
    {
        try {
            $options = ['headers' => $this->getHeaders()];
            $options['form_params'] = $parameters;
            $response = $this->client->request('POST', $this->getEndpointUrl('api', $endpoint), $options);
        } catch (ClientException $exception) {
            return $this->rpcEndpointRequest($endpoint, $parameters, true);
        }

        return json_decode($response->getBody(), true) ?? [];
    }
}
