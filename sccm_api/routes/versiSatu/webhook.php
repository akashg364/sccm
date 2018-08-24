<?php

    return [
    /**
     * @SWG\Post(
     *     path="/v1/webhook",
     *     summary="Create Webhook API",
     *     tags={"Webhook"},
     *     description="Description : This API will add path/URL of webhook endpoint, this URL is used to notify the client post the event if completed/failed.",
     *     @SWG\Parameter(
     *         name="body",
     *         in="body",
     *         description="Create Webhook",
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/CreateWebhook"),
     *     ),
     *     @SWG\Response(
     *         response=200,
     *         description="Webhook Response",
     *         @SWG\Schema(ref="#/definitions/CreateWebhook")
     *     ),
     *     @SWG\Response(
     *         response=422,
     *         description="ValidateErrorException",
     *         @SWG\Schema(ref="#/definitions/ErrorValidate")
     *     )
     * )
     */
    'POST webhook' => 'webhook/create',
    
];