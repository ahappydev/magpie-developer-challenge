<?php

namespace App;

use Symfony\Component\DomCrawler\Crawler;
use Symfony\Component\DomCrawler\UriResolver;

require 'vendor/autoload.php';

class Scrape
{
    private array $products = [];

    const MB_IN_GB = 1000;

    const PRODUCTS_URL = 'https://www.magpiehq.com/developer-challenge/smartphones/';

    public function run(): void
    {
        $document = ScrapeHelper::fetchDocument(self::PRODUCTS_URL);

        $pages = $document->filter('#pages')->filter('a');
        $products = $document->filter('.product');
        $pageCount = count($pages);
        $this->getProducts($products);

        for ($i = 2; $i <= $pageCount; $i++) {
            $document = ScrapeHelper::fetchDocument(self::PRODUCTS_URL."?page=".$i);
            $products = $document->filter('.product');
            $this->getProducts($products);
        }

        file_put_contents('output.json', json_encode($this->products, JSON_UNESCAPED_SLASHES));
    }

    /**
     * @param Crawler $products
     */
    private function getProducts(Crawler $products): void
    {
        foreach ($products as $product) {
            $this->productCount++;
            try {
                $productCrawler = new Crawler($product);

                // Get product colours as array
                $colours = $productCrawler->filter('span[data-colour]')->each(function (Crawler $node, $i) {
                    return $node->attr('data-colour');
                });

                // Get product name
                $name = $productCrawler->filter('.product-name')->text();

                // Get product capacity
                $capacityText = $productCrawler->filter('.product-capacity')->text();
                $capacityInt = (int)filter_var($capacityText, FILTER_SANITIZE_NUMBER_INT);

                $title = $name." ".$capacityText;

                // Convert capacity to MB if GB
                if (strpos($capacityText, 'GB') !== false) {
                    $capacityMB = $capacityInt * self::MB_IN_GB;
                } else {
                    $capacityMB = $capacityInt;
                }

                // Get product price
                $priceText = $productCrawler->filterXPath("//div[contains(text(), '£')]")->text();
                $priceFloat = (float) filter_var( $priceText, FILTER_SANITIZE_NUMBER_FLOAT, FILTER_FLAG_ALLOW_FRACTION);

                // Get product image src and resolve to absolute url
                $imageSrc = $productCrawler->filter('img')->attr('src');
                $imageUrl = UriResolver::resolve($imageSrc, self::PRODUCTS_URL);

                // Get product availability
                $availabilityText = $productCrawler->filterXPath("//div[contains(text(), 'Availability:')]")->text();
                $availabilityText = trim(substr($availabilityText,strrpos($availabilityText,':') + 1));
                $isAvailable = $availabilityText == "Out of Stock" ? false : true;

                // Get shipping text and date if exists
                $shippingText = null;
                $shippingDate = null;
                $shippingTextNode = $productCrawler->filterXPath("//div[contains(translate(text(), 
                                      'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                                      'abcdefghijklmnopqrstuvwxyz'), 'deliver')
                                       or contains(translate(text(), 
                                      'ABCDEFGHIJKLMNOPQRSTUVWXYZ',
                                      'abcdefghijklmnopqrstuvwxyz'), 'ship')]");
                if ($shippingTextNode->count()) {
                    $shippingText = $shippingTextNode->text();
                    $shippingDate = ScrapeHelper::extractDateFromString($shippingText);
                }

                foreach ($colours as $colour) {
                    $this->colorCount++;

                    $prod = new Product();
                    $prod->setTitle($title);
                    $prod->setPrice($priceFloat);
                    $prod->setImageUrl($imageUrl);
                    $prod->setCapacityMB($capacityMB);
                    $prod->setColour($colour);
                    $prod->setAvailabilityText($availabilityText);
                    $prod->setIsAvailable($isAvailable);
                    $prod->setShippingText($shippingText);
                    $prod->setShippingDate($shippingDate);

                    if (!in_array($prod,$this->products)) {
                        $this->products[] = $prod;
                    }
                }

            } catch (\Exception $e) {
                echo $e->getMessage();
            }
        }
    }

}

$scrape = new Scrape();
$scrape->run();
