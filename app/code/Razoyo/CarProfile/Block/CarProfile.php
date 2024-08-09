<?php
namespace Razoyo\CarProfile\Block;

use Magento\Framework\View\Element\Template;
use Magento\Framework\View\Element\Template\Context;
use Magento\Customer\Model\Session as CustomerSession;
use Magento\Framework\HTTP\Client\Curl;
use Psr\Log\LoggerInterface;
use Magento\Customer\Api\CustomerRepositoryInterface;
use Magento\Framework\App\ResourceConnection;

class CarProfile extends Template
{
    protected $context; // Declare the context property
    protected $customerSession;
    protected $curl;
    protected $logger;
    protected $customerRepository;
    protected $resourceConnection;

    public function __construct(
        Context $context,
        CustomerSession $customerSession,
        Curl $curl,
        LoggerInterface $logger,
        CustomerRepositoryInterface $customerRepository,
        ResourceConnection $resourceConnection,
        array $data = []
    ) {
        $this->context = $context; // Initialize the context property
        $this->customerSession = $customerSession->getCustomerId();
        $this->curl = $curl;
        $this->logger = $logger;
        $this->customerRepository = $customerRepository;
        $this->resourceConnection = $resourceConnection;
        parent::__construct($context, $data);
    }

    public function getCustomer()
    {
        if ($this->customerSession) {
            try {
                $customerId = $this->customerSession;
                $connection = $this->resourceConnection->getConnection();
                $tableName = $connection->getTableName('customer_entity_text');

                // Get attribute ID for 'car_profile'
                $attributeId = $connection->fetchOne(
                    "SELECT attribute_id FROM eav_attribute WHERE attribute_code = 'car_profile' AND entity_type_id = (SELECT entity_type_id FROM eav_entity_type WHERE entity_type_code = 'customer')"
                );

                if ($attributeId) {
                    // Fetch car profile data for the logged-in customer
                    $carProfileData = $connection->fetchOne(
                        $connection->select()
                            ->from($tableName, ['value'])
                            ->where('entity_id = ?', $customerId)
                            ->where('attribute_id = ?', $attributeId)
                    );
                }
                $this->logger->info('Car ID ' . $carProfileData);
                //$customerCarProfileID = $customer->getCustomAttribute('car_profile')->getValue();
                return $carProfileData;
            } catch (\Exception $e) {
                $this->logger->error('Error retrieving customer: ' . $e->getMessage());
            }
        } else {
            $this->logger->info('CarProfile.php: Customer is NOT logged in.');
        }
        return null;
    }

    public function getCarList()
    {
        $this->curl->get('https://exam.razoyo.com/api/cars');
        $response = $this->curl->getBody();
        $cars = json_decode($response, true);
        return $cars['cars'];
    }

    /**
     * Fetch the Bearer Token from the response header of the API
     */
    protected function fetchBearerToken()
    {
        try {
            $apiURL = 'https://exam.razoyo.com/api/cars';
            $this->curl->addHeader('Authorization', 'Bearer your_token');
            $this->curl->get($apiURL);
            $response = $this->curl->getBody();
            $headers = $this->curl->getHeaders();
            
            // Extract token from headers
            if (isset($headers['your-token'])) {
                return $headers['your-token'];
            }
            $this->logger->error('Bearer token not found in API response headers.');
        } catch (\Exception $e) {
            $this->logger->error('Error fetching bearer token: ' . $e->getMessage());
        }
        return null;
    }
    
    public function getCarData($carId)
    {
        $apiURL = 'https://exam.razoyo.com/api/cars/';
        $token = $this->fetchBearerToken();
        
        if ($token) {
            try {
                $this->curl->addHeader('Authorization', 'Bearer ' . $token);
                $this->curl->get($apiURL . urlencode($carId));
                $response = $this->curl->getBody();
                $this->logger->info('API Car Response'.$response);
                $carData = json_decode($response, true);
                if (json_last_error() === JSON_ERROR_NONE) {
                    return $carData;
                }
                $this->logger->error('Invalid JSON response from car data API.');
            } catch (\Exception $e) {
                $this->logger->error('Error fetching car data: ' . $e->getMessage());
            }
        }
        return null;
    }

    public function getFormKey()
    {
        $formKey = $this->context->getRequest()->getParam('form_key');
        $this->logger->info('Form Key Retrieved: ' . $formKey);
        return $formKey;
    }
}
