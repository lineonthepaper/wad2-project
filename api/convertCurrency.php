<?php
$method = $_SERVER["REQUEST_METHOD"];

if ($method === "POST") {
    try {
        $ch = curl_init();

        $endpoint = 'https://api.fxratesapi.com/latest';

        curl_setopt($ch, CURLOPT_RETURNTRANSFER, true);

        $payload = json_decode(file_get_contents('php://input'), true);
        if (!is_array($payload)) $payload = [];

        $params = [
            "api_key" => $_ENV["FXRATESAPI_KEY"],
            "base" => $payload["base"],
            "places" => $payload["places"],
            "currencies" => $payload["currencies"],
            "resolution" => $payload["resolution"],
            "amount" => $payload["amount"]
        ];

        $url = $endpoint . "?" . http_build_query($params);

        curl_setopt($ch, CURLOPT_URL, $url);

        $response = curl_exec($ch);

        if (curl_errno($ch)) {
            echo json_encode(["message" => "Caught exception " . curl_error($ch)]);
            exit;
        }

        echo $response;
    } catch (Exception $e) {
        echo json_encode(["message" => "Caught exception " . $e->getMessage()]);
        exit;
    }
}
