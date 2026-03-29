<?php

namespace App\Utils;

/**
 * Hierarchy Code Generator
 * Auto-generates unique codes for Godinas, Gamtas, and Gurmus
 * 
 * Pattern:
 * - Godina: 3-letter uppercase abbreviation from name (e.g., "USA", "CAN", "EUR")
 * - Gamta: {godina_code}-{3-letter suffix} (e.g., "USA-CAL", "CAN-EAST")
 * - Gurmu: {godina_code}-{gamta_suffix}-{3-letter suffix} (e.g., "USA-CAL-LAX", "CAN-EAST-TOR")
 */
class HierarchyCodeGenerator
{
    private $db = null;
    
    /**
     * Get Database instance (lazy loading)
     */
    private function getDb()
    {
        if ($this->db === null) {
            $this->db = Database::getInstance();
        }
        return $this->db;
    }
    
    /**
     * Generate unique Godina code from name
     * Examples: "United States" -> "USA", "Canada" -> "CAN", "Europe" -> "EUR"
     */
    public function generateGodinaCode(string $name): string
    {
        // Try to create 3-letter abbreviation from name
        $code = $this->createAbbreviation($name, 3);
        
        // Check if code exists, if so, append number
        $originalCode = $code;
        $counter = 1;
        
        while ($this->godinaCodeExists($code)) {
            $code = $originalCode . $counter;
            $counter++;
        }
        
        return strtoupper($code);
    }
    
    /**
     * Generate unique Gamta code
     * Pattern: {godina_code}-{abbreviation}
     */
    public function generateGamtaCode(int $godinaId, string $name): string
    {
        // Get Godina code
        $godina = $this->getDb()->fetch("SELECT code FROM godinas WHERE id = ?", [$godinaId]);
        
        if (!$godina) {
            throw new \Exception("Godina not found with ID: {$godinaId}");
        }
        
        $godinaCode = $godina['code'];
        
        // Create abbreviation for gamta name
        $suffix = $this->createAbbreviation($name, 4);
        $code = $godinaCode . '-' . $suffix;
        
        // Ensure uniqueness
        $originalCode = $code;
        $counter = 1;
        
        while ($this->gamtaCodeExists($code)) {
            $suffix = $this->createAbbreviation($name, 3) . $counter;
            $code = $godinaCode . '-' . $suffix;
            $counter++;
        }
        
        return strtoupper($code);
    }
    
    /**
     * Generate unique Gurmu code
     * Pattern: {godina_code}-{gamta_suffix}-{abbreviation}
     */
    public function generateGurmuCode(int $gamtaId, string $name): string
    {
        // Get Gamta and its Godina
        $gamta = $this->getDb()->fetch(
            "SELECT g.code as gamta_code, go.code as godina_code 
             FROM gamtas g 
             JOIN godinas go ON g.godina_id = go.id 
             WHERE g.id = ?",
            [$gamtaId]
        );
        
        if (!$gamta) {
            throw new \Exception("Gamta not found with ID: {$gamtaId}");
        }
        
        // Extract just the suffix part from gamta code (after the dash)
        $gamtaCodeParts = explode('-', $gamta['gamta_code']);
        $gamtaSuffix = end($gamtaCodeParts);
        
        // Create abbreviation for gurmu name
        $suffix = $this->createAbbreviation($name, 3);
        $code = $gamta['godina_code'] . '-' . $gamtaSuffix . '-' . $suffix;
        
        // Ensure uniqueness
        $originalCode = $code;
        $counter = 1;
        
        while ($this->gurmuCodeExists($code)) {
            $suffix = $this->createAbbreviation($name, 2) . $counter;
            $code = $gamta['godina_code'] . '-' . $gamtaSuffix . '-' . $suffix;
            $counter++;
        }
        
        return strtoupper($code);
    }
    
    /**
     * Create abbreviation from text
     * Examples:
     * - "United States" (3) -> "USA"
     * - "East Region" (4) -> "EAST"
     * - "Toronto" (3) -> "TOR"
     */
    private function createAbbreviation(string $text, int $length = 3): string
    {
        // Remove common words
        $text = preg_replace('/\b(the|and|of|or|region|community|oromo)\b/i', '', $text);
        $text = trim($text);
        
        // Split into words
        $words = preg_split('/[\s\-_]+/', $text);
        $words = array_filter($words); // Remove empty elements
        
        if (count($words) >= $length) {
            // Take first letter of each word
            $abbr = '';
            foreach (array_slice($words, 0, $length) as $word) {
                $abbr .= substr($word, 0, 1);
            }
            return $abbr;
        } elseif (count($words) > 1) {
            // Multiple words but less than desired length
            // Take more letters from first words
            $abbr = '';
            $remaining = $length;
            foreach ($words as $word) {
                $take = min($remaining, strlen($word));
                $abbr .= substr($word, 0, $take);
                $remaining -= $take;
                if ($remaining <= 0) break;
            }
            return substr($abbr, 0, $length);
        } else {
            // Single word - take first N characters
            return substr($text, 0, $length);
        }
    }
    
    /**
     * Check if Godina code exists
     */
    private function godinaCodeExists(string $code): bool
    {
        $result = $this->getDb()->fetch(
            "SELECT COUNT(*) as count FROM godinas WHERE code = ?",
            [strtoupper($code)]
        );
        return $result && $result['count'] > 0;
    }
    
    /**
     * Check if Gamta code exists
     */
    private function gamtaCodeExists(string $code): bool
    {
        $result = $this->getDb()->fetch(
            "SELECT COUNT(*) as count FROM gamtas WHERE code = ?",
            [strtoupper($code)]
        );
        return $result && $result['count'] > 0;
    }
    
    /**
     * Check if Gurmu code exists
     */
    private function gurmuCodeExists(string $code): bool
    {
        $result = $this->getDb()->fetch(
            "SELECT COUNT(*) as count FROM gurmus WHERE code = ?",
            [strtoupper($code)]
        );
        return $result && $result['count'] > 0;
    }
}
