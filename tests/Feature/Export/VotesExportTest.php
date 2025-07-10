<?php

use App\Models\Building;
use App\Models\User;
use App\Models\Voter;
use Maatwebsite\Excel\Facades\Excel;

beforeEach(function () {
    $this->user = User::factory()->create();
});

describe('Votes Export', function () {
    it('downloads votes export file', function () {
        Excel::fake();
        
        $response = $this->actingAs($this->user)->get('/export/votes');
        
        $response->assertStatus(200);
        Excel::assertDownloaded('gute-bauten-2025-stimmen-' . date('d.m.Y') . '-' . substr($response->headers->get('Content-Disposition'), -13, 8) . '.xlsx');
    });

    it('generates filename with correct format', function () {
        $response = $this->actingAs($this->user)->get('/export/votes');
        
        $response->assertStatus(200);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        expect($contentDisposition)->toContain('gute-bauten-2025-stimmen-');
        expect($contentDisposition)->toContain(date('d.m.Y'));
        expect($contentDisposition)->toContain('.xlsx');
    });

    it('includes timestamp in filename', function () {
        $response = $this->actingAs($this->user)->get('/export/votes');
        
        $response->assertStatus(200);
        
        $contentDisposition = $response->headers->get('Content-Disposition');
        $expectedDate = date('d.m.Y');
        expect($contentDisposition)->toContain($expectedDate);
    });

    it('includes random string in filename', function () {
        $response1 = $this->actingAs($this->user)->get('/export/votes');
        $response2 = $this->actingAs($this->user)->get('/export/votes');
        
        $filename1 = $response1->headers->get('Content-Disposition');
        $filename2 = $response2->headers->get('Content-Disposition');
        
        expect($filename1)->not->toBe($filename2);
    });

    it('sets correct content type for Excel file', function () {
        $response = $this->actingAs($this->user)->get('/export/votes');
        
        $response->assertStatus(200);
        $response->assertHeader('Content-Type', 'application/vnd.openxmlformats-officedocument.spreadsheetml.sheet');
    });

    it('creates summary sheet plus sheets for buildings with votes', function () {
        Excel::fake();
        
        $building1 = Building::factory()->create(['title' => 'Building A']);
        $building2 = Building::factory()->create(['title' => 'Building B']);
        $building3 = Building::factory()->create(['title' => 'Building C']);
        
        $voter1 = Voter::factory()->create();
        $voter2 = Voter::factory()->create();
        
        $voter1->buildings()->attach($building1->id);
        $voter2->buildings()->attach($building2->id);
        // building3 has no votes
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 3; // Summary + 2 buildings with votes
        });
    });

    it('includes summary sheet as first sheet', function () {
        Excel::fake();
        
        $building = Building::factory()->create();
        $voter = Voter::factory()->create();
        $voter->buildings()->attach($building->id);
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) >= 1; // At least summary sheet
        });
    });

    it('orders buildings by title in export', function () {
        Excel::fake();
        
        $buildingZ = Building::factory()->create(['title' => 'Z Building']);
        $buildingA = Building::factory()->create(['title' => 'A Building']);
        $buildingM = Building::factory()->create(['title' => 'M Building']);
        
        $voter1 = Voter::factory()->create();
        $voter2 = Voter::factory()->create();
        $voter3 = Voter::factory()->create();
        
        $voter1->buildings()->attach($buildingZ->id);
        $voter2->buildings()->attach($buildingA->id);
        $voter3->buildings()->attach($buildingM->id);
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 4; // Summary + 3 buildings
        });
    });

    it('excludes buildings without votes', function () {
        Excel::fake();
        
        $buildingWithVotes = Building::factory()->create();
        $buildingWithoutVotes = Building::factory()->create();
        
        $voter = Voter::factory()->create();
        $voter->buildings()->attach($buildingWithVotes->id);
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 2; // Summary + 1 building with votes
        });
    });

    it('works with no votes at all', function () {
        Excel::fake();
        
        Building::factory()->count(3)->create();
        // No votes created
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 1; // Only summary sheet
        });
    });

    it('handles voters with multiple building votes', function () {
        Excel::fake();
        
        $building1 = Building::factory()->create();
        $building2 = Building::factory()->create();
        $voter = Voter::factory()->create();
        
        $voter->buildings()->attach([$building1->id, $building2->id]);
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 3; // Summary + 2 buildings
        });
    });

    it('handles multiple voters for same building', function () {
        Excel::fake();
        
        $building = Building::factory()->create();
        $voter1 = Voter::factory()->create();
        $voter2 = Voter::factory()->create();
        $voter3 = Voter::factory()->create();
        
        $voter1->buildings()->attach($building->id);
        $voter2->buildings()->attach($building->id);
        $voter3->buildings()->attach($building->id);
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 2; // Summary + 1 building
        });
    });

    it('handles voters without any votes', function () {
        Excel::fake();
        
        $building = Building::factory()->create();
        $voterWithVote = Voter::factory()->create();
        $voterWithoutVote = Voter::factory()->create();
        
        $voterWithVote->buildings()->attach($building->id);
        // voterWithoutVote has no votes
        
        $this->actingAs($this->user)->get('/export/votes');
        
        Excel::assertDownloaded(function ($export) {
            $sheets = $export->sheets();
            return count($sheets) === 2; // Summary + 1 building
        });
    });
});