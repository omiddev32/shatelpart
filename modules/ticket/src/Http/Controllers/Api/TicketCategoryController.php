<?php

namespace App\Ticket\Http\Controllers\Api;

use App\Core\Controller;
use Illuminate\Http\Request;
use App\Ticket\Entities\TicketCategory;

class TicketCategoryController extends Controller
{
    /**
     * Get the ticket categories with data
     *
     * @route '/api/ticketing/categories'
     * @param Request $request
     * @return \Illuminate\Http\JsonResponse
     */
    public function getCategories(Request $request)
    {
        if(! auth()->user()->register_datetime) {
            return json_response([
                'error' => __("You are not allowed!")
            ], 403);
        }

        return json_response([
            'categories' => TicketCategory::with('ticketCategoryTopics.ticketCategoryTopicContents')->where('status', true)->get()->map(fn($category): array => [
                'id' => $category->id,
                'title' => $category->title,
                'description' => $category->description,
                'topics' => $category->ticketCategoryTopics->where('status', true)->map(fn($topic): array => [
                    'id' => $topic->id,
                    'title' => $topic->title,
                    'contents' => $topic->ticketCategoryTopicContents->where('status', true)->where('language', app()->getLocale())->map(fn($content): array => [
                        'id' => $content->id,
                        'title' => $content->title,
                        'link' => $content->link,
                    ])->toArray(),
                ])->toArray(),
            ])->toArray()
        ], 200);
    }
}
