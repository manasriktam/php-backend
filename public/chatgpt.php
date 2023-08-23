<?php


class CurlErrorException extends \Exception
{
};
class OpenAIErrorException extends \Exception
{
};

function send_chatgpt_message(
  mixed $payload,
  string $apiKey
): string {
  $ch = curl_init("https://api.openai.com/v1/chat/completions");

  $response_text = "";

  curl_setopt_array($ch, [
    CURLOPT_HTTPHEADER => [
      "Content-type: application/json",
      "Authorization: Bearer $apiKey"
    ],
    CURLOPT_POST => true,
    CURLOPT_POSTFIELDS => json_encode($payload),
    CURLOPT_RETURNTRANSFER => true,
    CURLOPT_WRITEFUNCTION => function ($ch, $data) use (&$response_text) {
      $json = json_decode($data);

      if (isset($json->error)) {
        http_response_code(401);
        $error  = $json->error->message;
        $error .= " (" . $json->error->code . ")";
        $error  = "`" . trim($error) . "`";

        echo "data: " . json_encode(["content" => $error]) . "\n\n";

        echo "event: stop\n";
        echo "data: stopped\n\n";

        flush();
        die();
      }

      echo $data;
      // $deltas = explode("\n", $data);

      // foreach ($deltas as $delta) {
      //   if (strpos($delta, "data: ") !== 0) {
      //     continue;
      //   }

      //   $json = json_decode(substr($delta, 6));

      //   if (isset($json->choices[0]->delta)) {
      //     $content = $json->choices[0]->delta->content ?? "";
      //   } elseif (trim($delta) == "data: [DONE]") {
      //     $content = "";
      //   } else {
      //     error_log("Invalid ChatGPT response: " . $delta);
      //   }

      //   $response_text .= $content;

      //   echo "data: " . json_encode(["content" => $content]) . "\n\n";
      // }

      flush();

      if (connection_aborted()) return 0;

      return strlen($data);
    }
  ]);

  $response = curl_exec($ch);

  if (!$response) {
    http_response_code(500);
    throw new CurlErrorException(sprintf(
      "Error in OpenAI request: %s",
      curl_errno($ch) . ": " . curl_error($ch)
    ));
  }

  // if (!$response_text) {
  //   http_response_code(500);
  //   throw new OpenAIErrorException(sprintf(
  //     "Unknown in OpenAI API request"
  //   ));
  // }

  return $response_text;
}
