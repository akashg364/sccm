<?php

return [

    /**
     * @SWG\Get(
     *     path="/v1/subservice",
     *     summary="Find Sub Service",
     *     tags={"Subservice list"},
     *     description="Description: This API will return User defined (UD) and System defined (SD) parameters based on managed or unmanaged, terminated device, routing protocol and topology type passed by the client in request.",
     *     @SWG\Parameter(
     *         name="service_id",
     *         in="query",
     *         description="Sub Service",
     *         type="integer", 
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Sccmcrd"),
     *     ),
     *      * @SWG\Parameter(
     *         name="is_managed",
     *         in="query",
     *         description="Sub Service",
     *         type="integer", 
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Sccmcrd"),
     *     ),
     *      * @SWG\Parameter(
     *         name="terminated_at",
     *         in="query",
     *         description="Sub Service",
     *         type="integer", 
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Sccmcrd"),
     *     ),
     *      * @SWG\Parameter(
     *         name="routing_protocol",
     *         in="query",
     *         description="Sub Service",
     *         type="integer", 
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Sccmcrd"),
     *     ),
     *     * @SWG\Parameter(
     *         name="dual",
     *         in="query",
     *         description="Sub Service",
     *         type="integer", 
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Sccmcrd"),
     *     ),
     *     * @SWG\Parameter(
     *         name="witheds",
     *         in="query",
     *         description="Sub Service",
     *         type="integer", 
     *         required=true,
     *         @SWG\Schema(ref="#/definitions/Sccmcrd"),
     *     ), 
     *     @SWG\Response(
     *         response=201,
     *         description="Sccmcrd",
     *         @SWG\Schema(ref="#/definitions/SubServiceWithFilters")
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'GET subservice' => 'subservice/get-sub-services',

];