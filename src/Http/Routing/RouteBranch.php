<?php

namespace Piper\Http\Routing;

final class RouteBranch
{
    /** @var PathFragment */
    private $fragment;

    /** @var RouteBranch[] */
    private $branches;

    /** @var RouteBranch[] */
    private $variableBranches;

    /** @var Route|null */
    private $route;

    public function __construct(PathFragment $fragment)
    {
        $this->fragment = $fragment;
        $this->branches = [];
        $this->variableBranches = [];
    }

    public static function root(): self
    {
        return new self(PathFragment::create('/'));
    }

    public function addRoute(Route $route, PathFragment ...$fragments): void
    {
        if ($fragments === []) {
            $this->route = $route;

            return;
        }

        /** @var PathFragment $fragment */
        $fragment = array_shift($fragments);
        $key = $fragment->toString();

        if (!isset($this->branches[$key])) {
            $branch = new RouteBranch($fragment);

            $this->branches[$key] = $branch;

            if ($fragment->isVariable()) {
                $this->variableBranches[] = $branch;
            }
        }

        $this->branches[$key]->addRoute($route, ...$fragments);
    }

    public function getRoute(PathFragment ...$fragments): ?Route
    {
        if ($fragments === []) {
            if ($this->route !== null) {
                return $this->route;
            }

            // try next branch with optional fragment
            $fragment = $this->emptyFragment();
        } else {
            $fragment = array_shift($fragments);
        }

        foreach ($this->nextBranchForFragment($fragment) as $nextBranch) {
            $route = $nextBranch->getRoute(...$fragments);

            if ($route !== null) {
                return $route;
            }
        }

        return null;
    }

    /**
     * @return \Generator|RouteBranch[]
     */
    private function nextBranchForFragment(PathFragment $fragment): \Generator
    {
        $key = $fragment->toString();
        $staticBranch = $this->branches[$key] ?? null;

        // branches that next fragment strictly matches given fragment
        if ($staticBranch !== null && $staticBranch->fragment->matches($fragment)) {
            yield $staticBranch;
        }

        // branches that next variable fragment matches given fragment
        yield from $this->branchesThatMatchesFragment($fragment);

        // branches that next fragment is optional
        yield from $this->branchesThatMatchesFragment($this->emptyFragment());
    }

    private function emptyFragment(): PathFragment
    {
        return PathFragment::empty();
    }

    private function branchesThatMatchesFragment(PathFragment $fragment): \Generator
    {
        foreach ($this->variableBranches as $variableBranch) {
            if ($variableBranch->fragment->matches($fragment)) {
                yield $variableBranch;
            }
        }
    }
}
