<?php
namespace App\Traits;
use Illuminate\Http\JsonResponse;
use Illuminate\Http\Response;
use Symfony\Component\HttpFoundation\BinaryFileResponse;

trait ApiResponser
{
    /**
     * success response method.
     *
     * @param        $result
     * @param   int  $code
     *
     * @return JsonResponse
     */
    protected function sendResponse($result, int $code = 200): JsonResponse
    {
        return response()->json($result, $code);
    }

    /**
     * file response method.
     *
     * @param         $path
     * @param   null  $filename
     *
     * @return BinaryFileResponse
     */
    protected function sendFileResponse($path, $filename = null): BinaryFileResponse
    {
        return response()->download($path, $filename);
    }

    /**
     * @return JsonResponse
     */
    protected function sendSuccess(): JsonResponse
    {
        return response()->json(['success' => true], 200);
    }

    /**
     * return error response.
     *
     * @param          $error
     * @param   int    $code
     *
     * @param   array  $errorMessages
     *
     * @return JsonResponse
     */
    protected function sendError($error, int $code = 400, array $errorMessages = []): JsonResponse
    {
        $response = [
            'success' => false,
            'message' => $error,
        ];


        if(!empty($errorMessages)){
            $response['data'] = $errorMessages;
        }


        return response()->json($response, $code);
    }

}
