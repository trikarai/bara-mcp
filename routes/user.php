<?php

$userAggregate = [
    'prefix' => '/api/user',
    'namespace' => 'User',
    'middleware' => 'userJwtAuth',
];

$router->group($userAggregate, function () use ($router) {
    $router->patch("/change-profile", ["uses" => "AccountController@changeProfile"]);
    $router->patch("/change-password", ["uses" => "AccountController@changePassword"]);
    $router->post('/file-uploads', ['uses' => "FileUploadController@upload"]);
    
    $router->group(['prefix' => '/programs'], function () use($router) {
        $controller = "ProgramController";
        $router->get("/{firmId}/{programId}", ["uses" => "$controller@show"]);
        $router->get("", ["uses" => "$controller@showAll"]);
    });
    
    $router->group(['prefix' => '/program-registrations'], function () use($router) {
        $controller = "ProgramRegistrationController";
        $router->post("", ["uses" => "$controller@register"]);
        $router->patch("/{programRegistrationId}/cancel", ["uses" => "$controller@cancel"]);
        $router->get("/{programRegistrationId}", ["uses" => "$controller@show"]);
        $router->get("", ["uses" => "$controller@showAll"]);
    });
    
    $router->group(['prefix' => '/program-participations'], function () use($router) {
        $controller = "ProgramParticipationController";
        $router->patch("/{programParticipationId}/quit", ["uses" => "$controller@quit"]);
        $router->get("/{programParticipationId}", ["uses" => "$controller@show"]);
        $router->get("", ["uses" => "$controller@showAll"]);
    });
    
    $asProgramParticipantAggregate = [
        'prefix' => '/as-program-participant/{firmId}/{programId}',
        'namespace' => 'AsProgramParticipant',
    ];
    $router->group($asProgramParticipantAggregate, function () use ($router) {
        $router->group(['prefix' => '/consultants'], function () use($router) {
            $controller = "ConsultantController";
            $router->get("/{consultantId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        $router->group(['prefix' => '/missions'], function () use($router) {
            $controller = "MissionController";
            $router->get("/{missionId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        $missionAggregate = [
            'prefix' => '/missions/{missionId}',
            'namespace' => 'Mission',
        ];
        $router->group($missionAggregate, function () use ($router) {
            $router->group(['prefix' => '/learning-materials'], function () use($router) {
                $controller = "LearningMaterialController";
                $router->get("/{learningMaterialId}", ["uses" => "$controller@show"]);
                $router->get("", ["uses" => "$controller@showAll"]);
            });
        });
        
        $router->group(['prefix' => '/consultation-setups'], function () use($router) {
            $controller = "ConsultationSetupController";
            $router->get("/{consultationSetupId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
    });
    
    $programParticipationAggregate = [
        'prefix' => '/program-participations/{programParticipationId}',
        'namespace' => 'ProgramParticipation',
    ];
    $router->group($programParticipationAggregate, function () use ($router) {
        
        $router->get('/activity-logs', ['uses' => "ActivityLogController@showAll"]);
        
        $router->group(['prefix' => '/worksheets'], function () use($router) {
            $controller = "WorksheetController";
            $router->post("", ["uses" => "$controller@addRoot"]);
            $router->post("/{worksheetId}", ["uses" => "$controller@addBranch"]);
            $router->patch("/{worksheetId}", ["uses" => "$controller@update"]);
            $router->delete("/{worksheetId}", ["uses" => "$controller@remove"]);
            $router->get("/{worksheetId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        $worksheetAggregate = [
            'prefix' => '/worksheets/{worksheetId}',
            'namespace' => 'Worksheet',
        ];
        $router->group($worksheetAggregate, function () use ($router) {
            $router->group(['prefix' => '/comments'], function () use($router) {
                $controller = "CommentController";
                $router->post("", ["uses" => "$controller@submitNew"]);
                $router->post("/{commentId}", ["uses" => "$controller@reply"]);
                $router->get("/{commentId}", ["uses" => "$controller@show"]);
                $router->get("", ["uses" => "$controller@showAll"]);
            });
        });
        
        $router->group(['prefix' => '/consultation-requests'], function () use($router) {
            $controller = "ConsultationRequestController";
            $router->post("", ["uses" => "$controller@submit"]);
            $router->patch("/{consultationRequestId}/cancel", ["uses" => "$controller@cancel"]);
            $router->patch("/{consultationRequestId}/change-time", ["uses" => "$controller@changeTime"]);
            $router->patch("/{consultationRequestId}/accept", ["uses" => "$controller@accept"]);
            $router->get("/{consultationRequestId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        
        $router->group(['prefix' => '/consultation-sessions'], function () use($router) {
            $controller = "ConsultationSessionController";
            $router->put("/{consultationSessionId}/submit-report", ["uses" => "$controller@submitReport"]);
            $router->get("/{consultationSessionId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        
        $router->delete('/participant-comments/{participantCommentId}', ['uses' => "ParticipantCommentController@remove"]);
        
        $router->group(['prefix' => '/missions'], function () use($router) {
            $controller = "MissionController";
            $router->get("/by-position/{position}", ["uses" => "$controller@showByPosition"]);
            $router->get("/by-id/{missionId}", ["uses" => "$controller@show"]);
            $router->get("", ["uses" => "$controller@showAll"]);
        });
        
    });
    
});
