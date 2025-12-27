<?php

namespace App\Livewire\Settings\Email;

use App\Models\Settings\EmailConfiguration;
use Livewire\Attributes\Layout;
use Livewire\Attributes\Title;
use Livewire\Component;

#[Layout('components.layouts.settings')]
#[Title('Email Configuration')]
class Index extends Component
{
    public string $mailer = 'smtp';
    public string $host = '';
    public ?int $port = 587;
    public string $username = '';
    public string $password = '';
    public string $encryption = 'tls';
    public string $fromAddress = '';
    public string $fromName = '';
    public bool $isActive = false;

    public string $testEmail = '';

    // For displaying current effective config
    public string $effectiveHost = '';
    public string $effectiveFromAddress = '';

    public function mount()
    {
        $config = EmailConfiguration::getConfiguration();

        $this->mailer = $config->mailer ?? 'smtp';
        $this->host = $config->host ?? '';
        $this->port = $config->port ?? 587;
        $this->username = $config->username ?? '';
        $this->password = ''; // Never show password
        $this->encryption = $config->encryption ?? 'tls';
        $this->fromAddress = $config->from_address ?? '';
        $this->fromName = $config->from_name ?? '';
        $this->isActive = $config->is_active ?? false;

        $this->loadEffectiveConfig();
    }

    protected function loadEffectiveConfig()
    {
        // Show what's actually being used (DB or ENV)
        $this->effectiveHost = EmailConfiguration::get('mail.mailers.smtp.host', '');
        $this->effectiveFromAddress = EmailConfiguration::get('mail.from.address', '');
    }

    public function save()
    {
        $this->validate([
            'mailer' => 'required|in:smtp,sendmail,mailgun,ses,postmark,log',
            'host' => 'nullable|string|max:255',
            'port' => 'nullable|integer|min:1|max:65535',
            'username' => 'nullable|string|max:255',
            'password' => 'nullable|string|max:255',
            'encryption' => 'nullable|in:tls,ssl,',
            'fromAddress' => 'nullable|email|max:255',
            'fromName' => 'nullable|string|max:255',
        ]);

        $config = EmailConfiguration::getConfiguration();

        $config->mailer = $this->mailer;
        $config->host = $this->host ?: null;
        $config->port = $this->port ?: 587;
        $config->username = $this->username ?: null;
        
        // Only update password if provided
        if ($this->password) {
            $config->password = $this->password;
        }
        
        $config->encryption = $this->encryption ?: null;
        $config->from_address = $this->fromAddress ?: null;
        $config->from_name = $this->fromName ?: null;
        $config->is_active = $this->isActive;

        $config->save();

        // Apply new config immediately
        EmailConfiguration::applyToConfig();

        $this->password = ''; // Clear password field
        $this->loadEffectiveConfig();

        session()->flash('success', 'Email configuration saved successfully!');
    }

    public function toggleActive()
    {
        $this->isActive = !$this->isActive;
        
        // Auto-save when toggling
        $config = EmailConfiguration::getConfiguration();
        $config->is_active = $this->isActive;
        $config->save();
        
        // Apply or remove config
        if ($this->isActive) {
            EmailConfiguration::applyToConfig();
        }
        
        $this->loadEffectiveConfig();
    }

    public function sendTestEmail()
    {
        $this->validate([
            'testEmail' => 'required|email',
        ]);

        try {
            // Apply config before sending
            EmailConfiguration::applyToConfig();

            \Illuminate\Support\Facades\Mail::raw(
                "This is a test email from your application.\n\nConfiguration source: " . 
                ($this->isActive ? 'Database' : 'Environment'),
                function ($message) {
                    $message->to($this->testEmail)
                        ->subject('Test Email - ' . config('app.name'));
                }
            );

            session()->flash('success', 'Test email sent successfully to ' . $this->testEmail);
        } catch (\Exception $e) {
            session()->flash('error', 'Failed to send test email: ' . $e->getMessage());
        }
    }

    public function render()
    {
        return view('livewire.settings.email.index');
    }
}
