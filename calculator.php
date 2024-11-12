<?php

function getCoordinates($address, $apiKey) {
    $url = "https://api.openrouteservice.org/geocode/search?api_key=$apiKey&text=" . urlencode($address);
    
    $response = file_get_contents($url);
    $data = json_decode($response, true);
    
    if (isset($data['features'][0]['geometry']['coordinates'])) {
        $coordinates = $data['features'][0]['geometry']['coordinates'];
        return [
            'longitude' => $coordinates[0],
            'latitude' => $coordinates[1]
        ];
    }
    
    return null; // Return null if geocoding fails
}

function calculateDistance($source, $destination, $apiKey) {
    $sourceCoords = getCoordinates($source, $apiKey);
    $destinationCoords = getCoordinates($destination, $apiKey);
    
    if ($sourceCoords && $destinationCoords) {
        $url = "https://api.openrouteservice.org/v2/directions/driving-car?api_key=$apiKey";
        
        $body = [
            'coordinates' => [
                [$sourceCoords['longitude'], $sourceCoords['latitude']],
                [$destinationCoords['longitude'], $destinationCoords['latitude']]
            ]
        ];
        
        $options = [
            'http' => [
                'header' => "Content-Type: application/json\r\n",
                'method' => 'POST',
                'content' => json_encode($body)
            ]
        ];
        
        $context = stream_context_create($options);
        $response = file_get_contents($url, false, $context);
        $data = json_decode($response, true);
        
        if (isset($data['routes'][0]['summary']['distance'])) {
            return $data['routes'][0]['summary']['distance'] / 1000; // Convert meters to kilometers
        }
    }
    
    return null; // Return null if distance calculation fails
}

function calculateTransportCost($source, $destination, $ratePerKm, $apiKey) {
    $distance = calculateDistance($source, $destination, $apiKey);
    
    if ($distance !== null) {
        return $distance * $ratePerKm;
    }
    
    return null; // Return null if calculation fails
}

// Usage example
$apiKey = '5b3ce3597851110001cf6248907eddd2959348eeace16ed957a6629d';
$source = "123 Source St, City A";
$destination = "456 Destination Ave, City B";
$ratePerKm = 5.0; // Example rate per kilometer

$cost = calculateTransportCost($source, $destination, $ratePerKm, $apiKey);

if ($cost !== null) {
    echo "The estimated transport cost is $" . number_format($cost, 2);
} else {
    echo "Could not calculate transport cost. Please check the input addresses.";
}
