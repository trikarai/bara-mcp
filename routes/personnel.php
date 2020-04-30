<?php

$personnelAggregate = [
    'prefix' => '/api/personnel',
    'namespace' => 'Personnel',
    'middleware' => 'personnelJwtAuth',
];
$router->group($personnelAggregate, function () use ($router) {
    $router->patch("/update-profile", ["uses" => "AccountController@updateProfile"]);
    $router->patch("/change-password", ["uses" => "AccountController@changePassword"]);
    $router->post('/file-uploads', ['uses' => "FileUploadController@upload"]);
    
    $asProgramCoordinatorAggregate = [
        'prefix' => '/as-program-coordinator/{programId}',
        'namespace' => 'AsProgramCoordinator',
    ];
    $router->group($asProgramCoordinatorAggregate, function () use ($router) {
        $router->group(['prefix' => '/registrants'], function () use($router) {
            $controller = "RegistrantController";
            $router->patch("/{registrantId}/accept", ["uses" => "$controller@accept"]);
            $router->patch("/{registrantId}/reject", ["uses" => "$controller@reject"]);
            $router->get("/{registrantId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
    });
    
    $programConsultantAggregate = [
        'prefix' => '/program-consultant/{programConsultantId}',
        'namespace' => 'ProgramConsultant',
    ];
    $router->group($programConsultantAggregate, function () use ($router) {
        $router->group(['prefix' => '/consultation-requests'], function () use($router) {
            $controller = "ConsultationRequestController";
            $router->patch("/{consultationRequestId}/accept", ["uses" => "$controller@accept"]);
            $router->patch("/{consultationRequestId}/offer", ["uses" => "$controller@offer"]);
            $router->patch("/{consultationRequestId}/reject", ["uses" => "$controller@reject"]);
            $router->get("/{consultationRequestId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        $router->group(['prefix' => '/consultation-sessions'], function () use($router) {
            $controller = "ConsultationSessionController";
            $router->put("/{consultationSessionIdId}/consultant-feedback", ["uses" => "$controller@setConsultantFeedback"]);
            $router->get("/{consultationSessionIdId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        $router->group(['prefix' => '/consultant-comments'], function () use($router) {
            $controller = "ConsultantCommentController";
            $router->post("/new", ["uses" => "$controller@submitNew"]);
            $router->post("/reply", ["uses" => "$controller@submitReply"]);
            $router->delete("/{consultantCommentId}", ["uses" => "$controller@remove"]);
        });
    });
    
});
