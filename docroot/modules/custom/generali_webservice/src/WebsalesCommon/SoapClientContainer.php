<?php

namespace Drupal\generali_webservice\WebsalesCommon;

use Symfony\Component\DependencyInjection\Argument\RewindableGenerator;
use Symfony\Component\DependencyInjection\ContainerInterface;
use Symfony\Component\DependencyInjection\Container;
use Symfony\Component\DependencyInjection\Exception\InvalidArgumentException;
use Symfony\Component\DependencyInjection\Exception\LogicException;
use Symfony\Component\DependencyInjection\Exception\RuntimeException;
use Symfony\Component\DependencyInjection\ParameterBag\FrozenParameterBag;

/*
 * This class has been auto-generated
 * by the Symfony Dependency Injection Component.
 *
 * @final since Symfony 3.3
 */
class SoapClientContainer extends Container
{
    private $parameters = [];
    private $targetDirs = [];

    public function __construct()
    {
        $this->parameters = $this->getDefaultParameters();

        $this->services = [];
        $this->methodMap = [
            'goetas_webservices.soap_client.metadata_reader' => 'getGoetasWebservices_SoapClient_MetadataReaderService',
        ];
        $this->privates = [
            'goetas_webservices.soap_client.metadata_reader' => true,
        ];

        $this->aliases = [];
    }

    public function getRemovedIds()
    {
        return [
            'Psr\\Container\\ContainerInterface' => true,
            'Symfony\\Component\\DependencyInjection\\ContainerInterface' => true,
            'goetas_webservices.soap_client.metadata.generator' => true,
            'goetas_webservices.soap_client.metadata_loader.array' => true,
            'goetas_webservices.soap_client.metadata_loader.dev' => true,
            'goetas_webservices.soap_client.metadata_reader' => true,
            'goetas_webservices.soap_client.stub.class_writer' => true,
            'goetas_webservices.soap_client.stub.client_generator' => true,
            'goetas_webservices.wsdl2php.converter.jms' => true,
            'goetas_webservices.wsdl2php.converter.php' => true,
            'goetas_webservices.wsdl2php.event_dispatcher' => true,
            'goetas_webservices.wsdl2php.soap_reader' => true,
            'goetas_webservices.wsdl2php.wsdl_reader' => true,
            'goetas_webservices.xsd2php.class_writer.php' => true,
            'goetas_webservices.xsd2php.converter.jms' => true,
            'goetas_webservices.xsd2php.converter.php' => true,
            'goetas_webservices.xsd2php.naming_convention' => true,
            'goetas_webservices.xsd2php.naming_convention.long' => true,
            'goetas_webservices.xsd2php.naming_convention.short' => true,
            'goetas_webservices.xsd2php.path_generator.jms' => true,
            'goetas_webservices.xsd2php.path_generator.jms.psr4' => true,
            'goetas_webservices.xsd2php.path_generator.php' => true,
            'goetas_webservices.xsd2php.path_generator.php.psr4' => true,
            'goetas_webservices.xsd2php.php.class_generator' => true,
            'goetas_webservices.xsd2php.schema_reader' => true,
            'goetas_webservices.xsd2php.writer.jms' => true,
            'goetas_webservices.xsd2php.writer.php' => true,
            'logger' => true,
        ];
    }

    public function compile()
    {
        throw new LogicException('You cannot compile a dumped container that was already compiled.');
    }

    public function isCompiled()
    {
        return true;
    }

    public function isFrozen()
    {
        @trigger_error(sprintf('The %s() method is deprecated since Symfony 3.3 and will be removed in 4.0. Use the isCompiled() method instead.', __METHOD__), E_USER_DEPRECATED);

        return true;
    }

    /*
     * Gets the private 'goetas_webservices.soap_client.metadata_reader' shared service.
     *
     * @return \GoetasWebservices\SoapServices\SoapClient\Metadata\Loader\ArrayMetadataLoader
     */
    protected function getGoetasWebservices_SoapClient_MetadataReaderService()
    {
        return $this->services['goetas_webservices.soap_client.metadata_reader'] = new \GoetasWebservices\SoapServices\SoapClient\Metadata\Loader\ArrayMetadataLoader($this->parameters['goetas_webservices.soap_client.metadata']);
    }

    public function getParameter($name)
    {
        $name = (string) $name;
        if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
            $name = $this->normalizeParameterName($name);

            if (!(isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters))) {
                throw new InvalidArgumentException(sprintf('The parameter "%s" must be defined.', $name));
            }
        }
        if (isset($this->loadedDynamicParameters[$name])) {
            return $this->loadedDynamicParameters[$name] ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
        }

        return $this->parameters[$name];
    }

    public function hasParameter($name)
    {
        $name = (string) $name;
        $name = $this->normalizeParameterName($name);

        return isset($this->parameters[$name]) || isset($this->loadedDynamicParameters[$name]) || array_key_exists($name, $this->parameters);
    }

    public function setParameter($name, $value)
    {
        throw new LogicException('Impossible to call set() on a frozen ParameterBag.');
    }

    public function getParameterBag()
    {
        if (null === $this->parameterBag) {
            $parameters = $this->parameters;
            foreach ($this->loadedDynamicParameters as $name => $loaded) {
                $parameters[$name] = $loaded ? $this->dynamicParameters[$name] : $this->getDynamicParameter($name);
            }
            $this->parameterBag = new FrozenParameterBag($parameters);
        }

        return $this->parameterBag;
    }

    private $loadedDynamicParameters = [];
    private $dynamicParameters = [];

    /*
     * Computes a dynamic parameter.
     *
     * @param string $name The name of the dynamic parameter to load
     *
     * @return mixed The value of the dynamic parameter
     *
     * @throws InvalidArgumentException When the dynamic parameter does not exist
     */
    private function getDynamicParameter($name)
    {
        throw new InvalidArgumentException(sprintf('The dynamic parameter "%s" must be defined.', $name));
    }

    private $normalizedParameterNames = [];

    private function normalizeParameterName($name)
    {
        if (isset($this->normalizedParameterNames[$normalizedName = strtolower($name)]) || isset($this->parameters[$normalizedName]) || array_key_exists($normalizedName, $this->parameters)) {
            $normalizedName = isset($this->normalizedParameterNames[$normalizedName]) ? $this->normalizedParameterNames[$normalizedName] : $normalizedName;
            if ((string) $name !== $normalizedName) {
                @trigger_error(sprintf('Parameter names will be made case sensitive in Symfony 4.0. Using "%s" instead of "%s" is deprecated since Symfony 3.4.', $name, $normalizedName), E_USER_DEPRECATED);
            }
        } else {
            $normalizedName = $this->normalizedParameterNames[$normalizedName] = (string) $name;
        }

        return $normalizedName;
    }

    /*
     * Gets the default parameters.
     *
     * @return array An array of the default parameters
     */
    protected function getDefaultParameters()
    {
        return [
            'goetas_webservices.soap_client.metadata' => [
                'modules/custom/generali_webservice/config/WebsalesCommon/CommonExternalWS01.wsdl' => [
                    'CommonExternalWS01' => [
                        'CommonServiceSoap_Port' => [
                            'operations' => [
                                'FindBroker' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/FindBroker',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'FindBroker',
                                    'method' => 'findBroker',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\FindBrokerInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\FindBrokerInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\FindBrokerInput',
                                        'parts' => [
                                            'parameters' => 'FindBrokerRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\FindBrokerOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\FindBrokerOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\FindBrokerOutput',
                                        'parts' => [
                                            'parameters' => 'FindBrokerResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'GetListLocalities' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/GetListLocalities',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'GetListLocalities',
                                    'method' => 'getListLocalities',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetListLocalitiesInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetListLocalitiesInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetListLocalitiesInput',
                                        'parts' => [
                                            'parameters' => 'GetListLocalitiesRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetListLocalitiesOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetListLocalitiesOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetListLocalitiesOutput',
                                        'parts' => [
                                            'parameters' => 'GetListLocalitiesResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'XferLead' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/XferLead',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'XferLead',
                                    'method' => 'xferLead',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\XferLeadInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\XferLeadInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\XferLeadInput',
                                        'parts' => [
                                            'parameters' => 'XferLeadRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\XferLeadOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\XferLeadOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\XferLeadOutput',
                                        'parts' => [
                                            'parameters' => 'XferLeadResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'RetrieveBrokerDetails' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/RetrieveBrokerDetails',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'RetrieveBrokerDetails',
                                    'method' => 'retrieveBrokerDetails',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\RetrieveBrokerDetailsInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\RetrieveBrokerDetailsInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\RetrieveBrokerDetailsInput',
                                        'parts' => [
                                            'parameters' => 'RetrieveBrokerDetailsRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\RetrieveBrokerDetailsOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\RetrieveBrokerDetailsOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\RetrieveBrokerDetailsOutput',
                                        'parts' => [
                                            'parameters' => 'RetrieveBrokerDetailsResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'FindQuittances' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/FindQuittances',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'FindQuittances',
                                    'method' => 'findQuittances',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\FindQuittancesInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\FindQuittancesInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\FindQuittancesInput',
                                        'parts' => [
                                            'parameters' => 'FindQuittancesRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\FindQuittancesOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\FindQuittancesOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\FindQuittancesOutput',
                                        'parts' => [
                                            'parameters' => 'FindQuittancesResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'GetOnlineServiceStatus' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/GetOnlineServiceStatus',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'GetOnlineServiceStatus',
                                    'method' => 'getOnlineServiceStatus',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetOnlineServiceStatusInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetOnlineServiceStatusInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetOnlineServiceStatusInput',
                                        'parts' => [
                                            'parameters' => 'GetOnlineServiceStatusRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetOnlineServiceStatusOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetOnlineServiceStatusOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetOnlineServiceStatusOutput',
                                        'parts' => [
                                            'parameters' => 'GetOnlineServiceStatusResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'GetParamTable' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/GetParamTable',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'GetParamTable',
                                    'method' => 'getParamTable',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetParamTableInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetParamTableInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetParamTableInput',
                                        'parts' => [
                                            'parameters' => 'GetParamTableRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetParamTableOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetParamTableOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetParamTableOutput',
                                        'parts' => [
                                            'parameters' => 'GetParamTableResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'FindBroker2' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/FindBroker2',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'FindBroker2',
                                    'method' => 'findBroker2',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\FindBroker2Input',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\FindBroker2Input',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\FindBroker2Input',
                                        'parts' => [
                                            'parameters' => 'FindBroker2Request',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\FindBroker2Output',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\FindBroker2Output',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\FindBroker2Output',
                                        'parts' => [
                                            'parameters' => 'FindBroker2Response',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'RetrieveInspectorDetails' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/RetrieveInspectorDetails',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'RetrieveInspectorDetails',
                                    'method' => 'retrieveInspectorDetails',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\RetrieveInspectorDetailsInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\RetrieveInspectorDetailsInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\RetrieveInspectorDetailsInput',
                                        'parts' => [
                                            'parameters' => 'RetrieveInspectorDetailsRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\RetrieveInspectorDetailsOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\RetrieveInspectorDetailsOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\RetrieveInspectorDetailsOutput',
                                        'parts' => [
                                            'parameters' => 'RetrieveInspectorDetailsResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                                'GetParameters' => [
                                    'action' => 'http://www.generali.be/ExternalWebservice01/GetParameters',
                                    'style' => 'document',
                                    'version' => '1.1',
                                    'name' => 'GetParameters',
                                    'method' => 'getParameters',
                                    'input' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetParametersInput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetParametersInput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetParametersInput',
                                        'parts' => [
                                            'parameters' => 'GetParametersRequest',
                                        ],
                                    ],
                                    'output' => [
                                        'message_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Messages\\GetParametersOutput',
                                        'headers_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapEnvelope\\Headers\\GetParametersOutput',
                                        'part_fqcn' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01\\SoapParts\\GetParametersOutput',
                                        'parts' => [
                                            'parameters' => 'GetParametersResponse',
                                        ],
                                    ],
                                    'fault' => [

                                    ],
                                ],
                            ],
                            'unwrap' => false,
                            'endpoint' => 'https://b2b.be.athora.com/websales_common/service.svc',
                        ],
                    ],
                ],
            ],
            'goetas_webservices.soap_client.config' => [
                'namespaces' => [
                    'http://www.generali.be/ExternalWebservice01' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\ExternalWebservice01',
                    'http://www.generali.be/FrontNameTypes' => 'Drupal\\generali_webservice\\WebsalesCommon\\Php\\FrontNameTypes',
                ],
                'destinations_php' => [
                    'Drupal\\generali_webservice\\WebsalesCommon' => 'modules/custom/generali_webservice/src/WebsalesCommon',
                ],
                'destinations_jms' => [
                    'Drupal\\generali_webservice\\WebsalesCommon' => 'modules/custom/generali_webservice/src/WebsalesCommon/Metadata',
                ],
                'metadata' => [
                    'modules/custom/generali_webservice/config/WebsalesCommon/CommonExternalWS01.wsdl' => NULL,
                ],
                'alternative_endpoints' => [

                ],
                'unwrap_returns' => false,
                'naming_strategy' => 'short',
                'path_generator' => 'psr4',
                'known_locations' => [

                ],
                'aliases' => [

                ],
                'headers' => '\\SoapEnvelope\\Headers',
                'parts' => '\\SoapEnvelope\\Parts',
                'messages' => '\\SoapEnvelope\\Messages',
            ],
            'goetas_webservices.soap_client.unwrap_returns' => false,
        ];
    }
}
