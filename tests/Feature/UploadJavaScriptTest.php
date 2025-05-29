<?php

namespace Tests\Feature;

use Tests\DuskTestCase;
use Laravel\Dusk\Browser;
use Illuminate\Foundation\Testing\DatabaseMigrations;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;

class UploadJavaScriptTest extends DuskTestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** @test */
    public function it_shows_drag_drop_interface()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->assertSee('Drag & Drop files here')
                ->assertSee('or click to select files')
                ->assertPresent('.upload-area');
        });
    }

    /** @test */
    public function it_shows_file_preview_after_selection()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->attach('input[type="file"]', __DIR__ . '/fixtures/test.pdf')
                ->waitFor('.file-preview-item')
                ->assertPresent('.file-preview-item')
                ->assertSee('test.pdf')
                ->assertPresent('.file-progress');
        });
    }

    /** @test */
    public function it_shows_multiple_file_previews()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->attach('input[type="file"]', [
                    __DIR__ . '/fixtures/test1.pdf',
                    __DIR__ . '/fixtures/test2.jpg'
                ])
                ->waitFor('.file-preview-item')
                ->assertPresent('.file-preview-item:nth-child(1)')
                ->assertPresent('.file-preview-item:nth-child(2)')
                ->assertSee('test1.pdf')
                ->assertSee('test2.jpg');
        });
    }

    /** @test */
    public function it_shows_upload_progress()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->attach('input[type="file"]', __DIR__ . '/fixtures/test.pdf')
                ->waitFor('.file-preview-item')
                ->assertPresent('.progress')
                ->assertPresent('.progress-bar')
                ->waitFor('.upload-success', 10)
                ->assertPresent('.upload-success');
        });
    }

    /** @test */
    public function it_shows_error_for_invalid_file()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->attach('input[type="file"]', __DIR__ . '/fixtures/test.php')
                ->waitFor('.file-preview-item')
                ->waitFor('.upload-error', 10)
                ->assertPresent('.upload-error')
                ->assertSee('Invalid file type');
        });
    }

    /** @test */
    public function it_allows_removing_files_before_upload()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->attach('input[type="file"]', __DIR__ . '/fixtures/test.pdf')
                ->waitFor('.file-preview-item')
                ->click('.remove-file')
                ->pause(500)
                ->assertMissing('.file-preview-item')
                ->assertMissing('.upload-progress-container');
        });
    }

    /** @test */
    public function it_shows_overall_progress_for_multiple_files()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->attach('input[type="file"]', [
                    __DIR__ . '/fixtures/test1.pdf',
                    __DIR__ . '/fixtures/test2.jpg'
                ])
                ->waitFor('.file-preview-item')
                ->assertPresent('.overall-progress')
                ->assertPresent('.progress-bar')
                ->waitFor('.upload-success', 10)
                ->assertPresent('.upload-success');
        });
    }

    /** @test */
    public function it_redirects_after_successful_upload()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->attach('input[type="file"]', __DIR__ . '/fixtures/test.pdf')
                ->waitFor('.file-preview-item')
                ->waitFor('.upload-success', 10)
                ->pause(1500) // Wait for redirect
                ->assertPathIs('/files');
        });
    }

    /** @test */
    public function it_shows_drag_over_state()
    {
        $this->browse(function (Browser $browser) {
            $browser->visit('/upload')
                ->script([
                    "const dropArea = document.querySelector('.upload-area');",
                    "const dragEvent = new DragEvent('dragover', {
                        bubbles: true,
                        cancelable: true,
                    });",
                    "dropArea.dispatchEvent(dragEvent);"
                ])
                ->assertPresent('.upload-area.dragover');
        });
    }
} 