<?php
require __DIR__ . '/vendor/autoload.php';
use GuzzleHttp\Client;

$client = new Client();
$RESULT_URL = "https://storage.googleapis.com/vocodes-public";
$INFERENCE_URL = "https://api.fakeyou.com/tts/inference";
$JOB_URL = "https://api.fakeyou.com/tts/job/%s";
$OUTPUT_DIR = __DIR__ . "/audio/";
$SLEEPTIME_SEC = 3;
$voices = [
    "papafrancesco" => "TM:8bqjb9x51vz3",
    "gerryscotti" => "TM:5ggf3m5w2mhq",
    "mariadefilippi" => "TM:7r48p42sbqej",
    "benigni" => "TM:vjfm5tdz02b2",
    "berlusconi" => "TM:22e5sxvt2dvk",
    "conte" => "TM:xv3f44tdztjs",
];

$input_voice = $argv[1];
$input_text = $argv[2];

if(!in_array($input_voice, array_keys($voices))) {
    echo sprintf("Invalid voice \n");
    die;
}

if(strlen($input_text) == 0 || strlen($input_text) > 100) {
    echo sprintf("Invalid text \n");
    die;
}

echo sprintf("Request sent - Waiting for response... \n");

$response = $client->request('POST', $INFERENCE_URL, [
    'headers' => [
        'Accept' => 'application/json',
        'Content-Type' => 'application/json',
    ],
    'body' => json_encode([
        "uuid_idempotency_token" => uniqid(), 
        "tts_model_token" => $voices[$input_voice], 
        "inference_text" => $input_text
     ])
]);

$responseString = (string) $response->getBody();
$responseArray = json_decode($responseString, true);
$inferenceToken = $responseArray["inference_job_token"];

echo sprintf("Response received - Token: %s \n", $inferenceToken);

do {
    sleep($SLEEPTIME_SEC);
    echo sprintf("Trying to fetch audio.. \n");

    $response = $client->request('GET', sprintf($JOB_URL, $inferenceToken), [
        'headers' => [
            'Accept' => 'application/json'
        ]
    ]);

    $responseString = (string) $response->getBody();
    $responseArray = json_decode($responseString, true);
    $status = $responseArray["state"]["status"];
} while(!in_array($status, ["complete_success", "complete_failure", "attempt_failed", "dead"]));

echo sprintf("Response received - Trying to download and convert audio \n");

if($status == "complete_success") {
    $url = $RESULT_URL . $responseArray["state"]["maybe_public_bucket_wav_audio_path"];

    $fileDir = __DIR__ . "/audio/";

    if (!file_exists($fileDir)) {
        mkdir($fileDir, 0777, true);
    }

    $file_name = basename($url);
    file_put_contents($fileDir . $file_name, file_get_contents($url));

    $wav = $fileDir . $file_name;
    $outputFilename =
        time()
        . '-'
        . strtoupper(str_replace(' ', '', $input_voice))
        . '-'
        . str_replace(' ', '_', substr($input_text,0,40));
    $mp3 = $fileDir . $outputFilename . ".mp3";
    $output = shell_exec(sprintf('lame -q0 -b128 %s %s 2>/dev/null', $wav, $mp3));

    unlink($wav);

    echo sprintf("Completed! File: %s \n", $mp3);

} else {
    echo sprintf("Failure: %s \n", $status);
}