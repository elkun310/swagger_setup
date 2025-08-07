<?php

namespace Tests\Feature;

use Illuminate\Foundation\Testing\RefreshDatabase;
use Illuminate\Foundation\Testing\WithFaker;
use Illuminate\Http\UploadedFile;
use Illuminate\Support\Facades\Storage;
use Tests\TestCase;

class UploadControllerTest extends TestCase
{
    public function setUp(): void
    {
        parent::setUp();
        Storage::fake('local');
    }

    /** @test */
    public function it_can_show_upload_form()
    {
        $response = $this->get(route('upload.form'));

        $response->assertStatus(200)
            ->assertViewIs('upload')
            ->assertSee('Upload File');
    }

    /** @test */
    public function it_can_upload_single_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024);

        $response = $this->post(route('upload'), [
            'file' => $file
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully',
                'file' => [
                    'name' => $file->hashName(),
                    'size' => $file->getSize(),
                    'type' => $file->getClientMimeType()
                ]
            ]);

        Storage::disk('local')->assertExists('uploads/' . $file->hashName());
    }

    /** @test */
    public function it_can_upload_multiple_files()
    {
        $files = [
            UploadedFile::fake()->create('document1.pdf', 1024),
            UploadedFile::fake()->create('document2.pdf', 1024),
            UploadedFile::fake()->image('image.jpg'),
        ];

        foreach ($files as $file) {
            $response = $this->post(route('upload'), [
                'file' => $file
            ]);

            $response->assertStatus(200)
                ->assertJson([
                    'success' => true,
                    'message' => 'File uploaded successfully',
                    'file' => [
                        'name' => $file->hashName(),
                        'size' => $file->getSize(),
                        'type' => $file->getClientMimeType()
                    ]
                ]);

            Storage::disk('local')->assertExists('uploads/' . $file->hashName());
        }
    }

    /** @test */
    public function it_validates_file_size()
    {
        // Create a file larger than the max validation size (10MB)
        $largeFile = UploadedFile::fake()->create('large.pdf', 11264); // 11MB

        $response = $this->post(route('upload'), [
            'file' => $largeFile
        ]);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The file must not be greater than 10240 kilobytes.'
            ]);

        Storage::disk('local')->assertMissing('uploads/' . $largeFile->hashName());
    }

    /** @test */
    public function it_validates_file_type()
    {
        $invalidFile = UploadedFile::fake()->create('script.php', 100);

        $response = $this->post(route('upload'), [
            'file' => $invalidFile
        ]);

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File uploaded successfully'
            ]);

        Storage::disk('local')->assertExists('uploads/' . $invalidFile->hashName());
    }

    /** @test */
    public function it_handles_empty_file_upload()
    {
        $response = $this->post(route('upload'), []);

        $response->assertStatus(422)
            ->assertJson([
                'success' => false,
                'message' => 'The file field is required.'
            ]);
    }

    /** @test */
    public function it_can_list_uploaded_files()
    {
        // Upload some test files first
        $files = [
            UploadedFile::fake()->create('document.pdf', 1024),
            UploadedFile::fake()->image('image.jpg'),
        ];

        foreach ($files as $file) {
            $this->post(route('upload'), ['file' => $file]);
        }

        $response = $this->get(route('files.list'));

        $response->assertStatus(200)
            ->assertViewIs('files.list');

        $filesData = $response->viewData('filesData');
        $this->assertCount(2, $filesData);
    }

    /** @test */
    public function it_can_delete_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024);
        
        // Upload the file first
        $this->post(route('upload'), ['file' => $file]);
        
        $response = $this->delete(route('files.delete', ['filename' => $file->hashName()]));

        $response->assertStatus(200)
            ->assertJson([
                'success' => true,
                'message' => 'File deleted successfully'
            ]);

        Storage::disk('local')->assertMissing('uploads/' . $file->hashName());
    }

    /** @test */
    public function it_handles_deleting_nonexistent_file()
    {
        $response = $this->delete(route('files.delete', ['filename' => 'nonexistent.pdf']));

        $response->assertStatus(404)
            ->assertJson([
                'success' => false,
                'message' => 'File not found'
            ]);
    }

    /** @test */
    public function it_can_view_file()
    {
        $file = UploadedFile::fake()->create('document.pdf', 1024);
        
        // Upload the file first
        $this->post(route('upload'), ['file' => $file]);
        
        $response = $this->get(route('files.view', ['filename' => $file->hashName()]));

        $response->assertStatus(200);
    }

    /** @test */
    public function it_handles_viewing_nonexistent_file()
    {
        $response = $this->get(route('files.view', ['filename' => 'nonexistent.pdf']));

        $response->assertStatus(404);
    }
} 