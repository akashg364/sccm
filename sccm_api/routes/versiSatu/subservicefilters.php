<?php

return [

    /**
     * @SWG\Get(
     *     path="/v1/subservicefilters",
     *     summary="Get List of SubService Filters",
     *     tags={"Subservicefilters"},
     *     description="Description : Get list of SubService filters.",
     *     @SWG\Response(
     *         response=200,
     *         description="SubServiceFilters",
     *         @SWG\Schema(
     *            type="array",
     *            @SWG\Items(ref="#/definitions/SubServiceFilters")
     *         )
     *     ),
     *     @SWG\Response(
     *        response=401,
     *        description="Unauthorized",
     *        @SWG\Schema(ref="#/definitions/Unauthorized")
     *     )
     * )
     */
    'GET subservicefilters' => 'subservicefilters/index',

];