<?php
declare(strict_types=1);

namespace App\Controllers;

use App\Auth\Auth;
use App\Core\Request;
use App\Core\Response;
use App\Core\View;
use App\Models\Document;

final class SearchController
{
    public function page(Request $req): Response
    {
        $query = is_scalar($req->query['q'] ?? null) ? trim((string) $req->query['q']) : '';
        $results = $query === '' ? [] : Document::searchForUser((int) Auth::id(), $query);

        return View::render('search/index', [
            'title' => 'Search',
            'q' => $query,
            'results' => $results,
        ]);
    }

    public function api(Request $req): Response
    {
        $query = is_scalar($req->query['q'] ?? null) ? trim((string) $req->query['q']) : '';
        if (mb_strlen($query) < 2) {
            return (new Response())->json(['results' => []]);
        }

        $results = array_map(
            static function (array $result): array {
                $result['snippet'] = safe_highlight((string) ($result['snippet'] ?? ''));

                return [
                    'id' => $result['id'],
                    'title' => $result['title'],
                    'workspace_slug' => $result['workspace_slug'],
                    'workspace_name' => $result['workspace_name'],
                    'snippet' => $result['snippet'],
                    'updated_at' => $result['updated_at'],
                ];
            },
            Document::searchForUser((int) Auth::id(), $query),
        );

        return (new Response())->json(['results' => $results]);
    }
}
