<?php

namespace App\Http\Controllers;

use App\Models\Skill;
use App\Models\SkillEndorsement;
use App\Models\UserSkill;
use Illuminate\Http\Request;
use Illuminate\Support\Facades\Auth;

class SkillController extends Controller
{
    public function index()
    {
$skills = Skill::orderBy('Category')->orderBy('Subcategory')->orderBy('Skill_Title')->get();

$userCouncil = Auth::user()->Council ?? null;

// Sort: user's council first, then others
if ($userCouncil) {
    $skills = $skills->sortBy(function ($skill) use ($userCouncil) {
        return $skill->Council === $userCouncil ? 0 : 1;
    })->values();
}

$groupedSkills = $skills->groupBy(function ($s) {
    return $s->Category ?: 'General';
})->map(function ($categorySkills) {
    return $categorySkills->groupBy(function ($s) {
        return $s->Subcategory ?: null;
    });
});

$user = Auth::user();
$showIntro = false;

if ($user) {
    $settings = $user->settings ?? [];
    if (empty($settings['skills_intro_seen'])) {
        $showIntro = true;
        $settings['skills_intro_seen'] = true;
        $user->settings = $settings;
        $user->save();
    }
}

$userSkills = Auth::id()
    ? $user->skills->pluck('Skill_ID')->toArray()
    : [];
$userSkillStatuses = Auth::id()
    ? $user->userSkills->pluck('Status', 'Skill_ID')->toArray()
    : [];

return view('skills.index', compact('skills', 'groupedSkills', 'userSkills', 'userSkillStatuses', 'showIntro'));
    }

    public function store(Request $request)
    {
        $validated = $request->validate([
            'skill_title' => ['required', 'string', 'max:100'],
            'category' => ['nullable', 'string', 'max:100'],
            'subcategory' => ['nullable', 'string', 'max:100'],
        ]);
        $skill = Skill::create($validated);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'skill' => $skill]);
        }

        return back()->with('success', 'Skill added.');
    }

public function addToMe(Request $request)
    {
        $request->validate([
            'skill_id' => ['required', 'integer', 'exists:skills,Skill_ID'],
            'proficiency' => ['nullable', 'integer', 'min:1', 'max:5'],
        ]);
        $proficiency = $request->input('proficiency', 3);

        \App\Models\UserSkill::firstOrCreate([
            'User_ID' => Auth::id(),
            'Skill_ID' => $request->skill_id,
        ], [
            'Proficiency' => $proficiency,
            'Status' => 'verified',
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true]);
        }

        return back()->with('success', 'Skill added.');
    }

    public function endorse(Request $request)
    {
        $request->validate([]);
        $userSkill = UserSkill::findOrFail($request->user_skill_id);

        if ($userSkill->User_ID === Auth::id()) {
            return back()->with('error', 'You cannot endorse your own skill.');
        }

        $endorsement = SkillEndorsement::firstOrCreate([
            'Endorser_ID' => Auth::id(),
            'User_Skill_ID' => $request->user_skill_id,
        ]);

        if ($request->wantsJson() || $request->ajax()) {
            return response()->json(['success' => true, 'endorsed' => $endorsement->wasRecentlyCreated]);
        }

        return back()->with('success', 'Skill endorsed successfully.');
    }
}
