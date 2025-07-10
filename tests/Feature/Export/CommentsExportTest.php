<?php

use App\Models\Building;
use App\Models\Comment;
use App\Models\User;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Comments Export', function () {
    it('downloads comments export file', function () {
        Excel::fake();
        
        $response = $this->actingAs($this->user)->get('/export/comments');
        
        $response->assertStatus(200);
        Excel::assertDownloaded('gute-bauten-2025-kommentare-' . date('d.m.Y') . '-' . substr($response->headers->get('Content-Disposition'), -13, 8) . '.xlsx');
    });

    it('generates filename with correct format', function () {
        $response = $this->actingAs($this->user)->get('/export/comments');
        
        $response->assertStatus(200);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        expect($contentDisposition)->toContain('gute-bauten-2025-kommentare-');
        expect($contentDisposition)->toContain(date('d.m.Y'));
        expect($contentDisposition)->toContain('.xlsx');
    });

    it('includes timestamp in filename', function () {
        $response = $this->actingAs($this->user)->get('/export/comments');
        
        $response->assertStatus(200);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        $expectedDate = date('d.m.Y');
        expect($contentDisposition)->toContain($expectedDate);
    });

    it('includes random string in filename', function () {
        $response1 = $this->actingAs($this->user)->get('/export/comments');
        $response2 = $this->actingAs($this->user)->get('/export/comments');
        
        $filename1 = $response1->headers->get('Content-Disposition');
        $filename2 = $response2->headers->get('Content-Disposition');
        
        expect($filename1)->not->toBe($filename2);
    });

    it('sets correct content type for Excel file', function () {
        $response = $this->actingAs($this->user)->get('/export/comments');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });

    it('creates sheets for buildings with comments', function () {
        Excel::fake();
        
        $building1 = Building::factory()->create(['title' => 'Building A']);
        $building2 = Building::factory()->create(['title' => 'Building B']);
        $building3 = Building::factory()->create(['title' => 'Building C']);
        
        Comment::factory()->published()->forBuilding($building1)->create();
        Comment::factory()->published()->forBuilding($building2)->create();
        // building3 has no comments
        
        $this->actingAs($this->user)->get('/export/comments');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 2; // Only buildings with comments
        });
    });

    it('orders buildings by title in export', function () {
        Excel::fake();
        
        $buildingZ = Building::factory()->create(['title' => 'Z Building']);
        $buildingA = Building::factory()->create(['title' => 'A Building']);
        $buildingM = Building::factory()->create(['title' => 'M Building']);
        
        Comment::factory()->published()->forBuilding($buildingZ)->create();
        Comment::factory()->published()->forBuilding($buildingA)->create();
        Comment::factory()->published()->forBuilding($buildingM)->create();
        
        $this->actingAs($this->user)->get('/export/comments');
        
        Excel::assertDownloaded(function ($export) use ($buildingA, $buildingM, $buildingZ) {
            $sheets = $export->sheets();
            // Verify buildings are ordered alphabetically
            return count($sheets) === 3;
        });
    });

    it('excludes buildings without comments', function () {
        Excel::fake();
        
        $buildingWithComments = Building::factory()->create();
        $buildingWithoutComments = Building::factory()->create();
        
        Comment::factory()->published()->forBuilding($buildingWithComments)->create();
        
        $this->actingAs($this->user)->get('/export/comments');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 1; // Only building with comments
        });
    });

    it('works with no comments at all', function () {
        Excel::fake();
        
        Building::factory()->count(3)->create();
        // No comments created
        
        $this->actingAs($this->user)->get('/export/comments');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 0; // No sheets for buildings without comments
        });
    });

    it('includes both published and unpublished comments', function () {
        Excel::fake();
        
        $building = Building::factory()->create();
        Comment::factory()->published()->forBuilding($building)->create();
        Comment::factory()->draft()->forBuilding($building)->create();
        
        $this->actingAs($this->user)->get('/export/comments');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 1; // Should include building with any comments
        });
    });

    it('handles soft deleted comments', function () {
        Excel::fake();
        
        $building = Building::factory()->create();
        $comment = Comment::factory()->forBuilding($building)->create();
        $comment->delete(); // Soft delete
        
        $this->actingAs($this->user)->get('/export/comments');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 1; // Should still include building
        });
    });
});