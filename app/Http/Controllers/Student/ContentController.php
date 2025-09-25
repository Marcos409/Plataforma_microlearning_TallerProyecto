<?php

namespace App\Http\Controllers\Student;

use App\Http\Controllers\Controller;
use App\Models\ContentLibrary;
use App\Models\LearningPath;
use App\Models\LearningPathContent;
use App\Models\Recommendation;
use App\Models\StudentProgress;
use Illuminate\Http\Request;

class ContentController extends Controller
{
    public function index(Request $request)
    {
        $query = ContentLibrary::where('active', true);

        // Filtros
        if ($request->filled('subject')) {
            $query->where('subject_area', $request->subject);
        }

        if ($request->filled('topic')) {
            $query->where('topic', $request->topic);
        }

        if ($request->filled('type')) {
            $query->where('type', $request->type);
        }

        if ($request->filled('difficulty')) {
            $query->where('difficulty_level', $request->difficulty);
        }

        if ($request->filled('search')) {
            $search = $request->search;
            $query->where(function($q) use ($search) {
                $q->where('title', 'like', "%{$search}%")
                  ->orWhere('description', 'like', "%{$search}%")
                  ->orWhere('tags', 'like', "%{$search}%");
            });
        }

        $contents = $query->orderBy('title')->paginate(12);

        // Obtener valores únicos para filtros
        $subjects = ContentLibrary::distinct()->pluck('subject_area');
        $topics = ContentLibrary::distinct()->pluck('topic');
        $types = ContentLibrary::distinct()->pluck('type');

        // Contenidos recomendados
        $recommended = Recommendation::where('user_id', auth()->id())
            ->where('is_completed', false)
            ->with('content')
            ->orderBy('priority')
            ->limit(3)
            ->get();

        return view('student.content.index', compact(
            'contents', 'subjects', 'topics', 'types', 'recommended'
        ));
    }

    public function show(ContentLibrary $content)
    {
        // Marcar recomendación como vista si existe
        $recommendation = Recommendation::where('user_id', auth()->id())
            ->where('content_id', $content->id)
            ->where('is_viewed', false)
            ->first();

        if ($recommendation) {
            $recommendation->update([
                'is_viewed' => true,
                'viewed_at' => now()
            ]);
        }

        // Obtener contenidos relacionados
        $relatedContents = ContentLibrary::where('subject_area', $content->subject_area)
            ->where('topic', $content->topic)
            ->where('id', '!=', $content->id)
            ->where('active', true)
            ->limit(4)
            ->get();

        return view('student.content.show', compact('content', 'relatedContents', 'recommendation'));
    }

    public function markAsComplete(Request $request, ContentLibrary $content)
    {
        $user = auth()->user();
        $timeSpent = $request->input('time_spent', 0);

        // Actualizar progreso del estudiante
        $progress = StudentProgress::firstOrCreate(
            [
                'user_id' => $user->id,
                'subject_area' => $content->subject_area,
                'topic' => $content->topic
            ],
            [
                'total_activities' => 0,
                'completed_activities' => 0,
                'progress_percentage' => 0,
                'average_score' => 0,
                'total_time_spent' => 0
            ]
        );

        $progress->increment('total_activities');
        $progress->increment('completed_activities');
        $progress->increment('total_time_spent', $timeSpent);
        $progress->progress_percentage = ($progress->completed_activities / $progress->total_activities) * 100;
        $progress->last_activity = now();
        $progress->save();

        // Marcar como completado en rutas de aprendizaje
        LearningPathContent::whereHas('learningPath', function($query) use ($user) {
            $query->where('user_id', $user->id);
        })->where('content_id', $content->id)
          ->where('is_completed', false)
          ->update([
              'is_completed' => true,
              'completed_at' => now(),
              'time_spent' => $timeSpent
          ]);

        // Actualizar progreso de rutas de aprendizaje
        $learningPaths = LearningPath::where('user_id', $user->id)
            ->whereHas('contents', function($query) use ($content) {
                $query->where('content_id', $content->id);
            })->get();

        foreach ($learningPaths as $path) {
            $path->updateProgress();
        }

        // Marcar recomendación como completada
        Recommendation::where('user_id', $user->id)
            ->where('content_id', $content->id)
            ->update([
                'is_completed' => true,
                'completed_at' => now()
            ]);

        return response()->json([
            'success' => true,
            'message' => 'Contenido marcado como completado'
        ]);
    }

    public function learningPaths()
    {
        $learningPaths = LearningPath::where('user_id', auth()->id())
            ->with(['contents.content'])
            ->orderBy('created_at', 'desc')
            ->get();

        return view('student.learning-paths.index', compact('learningPaths'));
    }

    public function showLearningPath(LearningPath $learningPath)
    {
        // Verificar que la ruta pertenezca al usuario autenticado
        if ($learningPath->user_id !== auth()->id()) {
            abort(403);
        }

        $learningPath->load(['contents.content']);

        return view('student.learning-paths.show', compact('learningPath'));
    }

    public function recommendations()
    {
        $recommendations = Recommendation::where('user_id', auth()->id())
            ->with('content')
            ->orderBy('priority')
            ->orderBy('created_at', 'desc')
            ->paginate(10);

        return view('student.recommendations.index', compact('recommendations'));
    }

    public function markRecommendationViewed(Recommendation $recommendation)
    {
        // Verificar que la recomendación pertenezca al usuario
        if ($recommendation->user_id !== auth()->id()) {
            abort(403);
        }

        $recommendation->update([
            'is_viewed' => true,
            'viewed_at' => now()
        ]);

        return response()->json(['success' => true]);
    }
}