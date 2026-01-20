<?php

class Recommendation {
    private function calculateMatch(array $studentInterests, string $category): float {
        $value = $studentInterests[$category] ?? 0;

        return ($value / 5) * 100;
    }

    public function getRecommendations(
        array $studentInterests,
        array $topics,
        int $top = 5
    ): array {

        $result = [];

        foreach ($topics as $topic) {
            $score = $this->calculateMatch(
                $studentInterests,
                $topic['category']
            );

            if ($score > 0) {
                $topic['match_score'] = $score;
                $result[] = $topic;
            }
        }

        usort($result, fn($a, $b) => $b['match_score'] <=> $a['match_score']);

        return array_slice($result, 0, $top);
    }
}