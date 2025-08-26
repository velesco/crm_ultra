<?php

namespace App\Models;

use Illuminate\Database\Eloquent\Factories\HasFactory;
use Illuminate\Database\Eloquent\Model;

class EmailTemplate extends Model
{
    use HasFactory;

    protected $fillable = [
        'name',
        'subject',
        'content',
        'variables',
        'is_active',
        'category',
        'created_by'
    ];

    protected $casts = [
        'variables' => 'array',
        'is_active' => 'boolean',
        'created_at' => 'datetime',
        'updated_at' => 'datetime',
    ];

    // Relationships
    public function creator()
    {
        return $this->belongsTo(User::class, 'created_by');
    }

    public function emailCampaigns()
    {
        return $this->hasMany(EmailCampaign::class, 'template_id');
    }

    // Scopes
    public function scopeActive($query)
    {
        return $query->where('is_active', true);
    }

    public function scopeByCategory($query, $category)
    {
        return $query->where('category', $category);
    }

    // Methods
    public function render($variables = [])
    {
        $content = $this->content;
        $subject = $this->subject;

        foreach ($variables as $key => $value) {
            $content = str_replace('{{' . $key . '}}', $value, $content);
            $subject = str_replace('{{' . $key . '}}', $value, $subject);
        }

        return [
            'subject' => $subject,
            'content' => $content
        ];
    }

    public function extractVariables()
    {
        $content = $this->content . ' ' . $this->subject;
        preg_match_all('/\{\{([^}]+)\}\}/', $content, $matches);
        
        return array_unique($matches[1]);
    }

    public function preview($sampleData = [])
    {
        $variables = $this->extractVariables();
        $previewData = [];
        
        foreach ($variables as $variable) {
            $previewData[$variable] = $sampleData[$variable] ?? "[{$variable}]";
        }
        
        return $this->render($previewData);
    }
}
