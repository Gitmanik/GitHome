<?php

class GitHomeRedirect implements GitPHPAction
{
    function __construct()
    {
        GitPHP::register_action("redirect", $this);
    }

    public function render($elements)
    {
        if (!isset($elements[1]))
            GitHome::die("GitHomeRedirect render: Redirect ID not specified!");
        
        $resolvedRedirect = $this->resolveTarget($elements[1]);
        
        if (is_null($resolvedRedirect))
            GitHome::die("GitHomeRedirect render: Redirect not available!");

        GitPHP::handleElements(explode('/', $resolvedRedirect));
    }

    private function resolveTarget($id)
    {
        if (!isset($id))
            GitHome::die("GitHomeRedirect resolveTarget: No ID provived.");
    
        $stmt = GitHome::db()->prepare("SELECT target FROM redirect WHERE id = :id");
        $stmt->bindValue(":id", $id);
        $stmt->execute();
        $stmt->bindColumn("target", $resolvedTarget);
        $stmt->fetch();

        return $resolvedTarget;
    }
}

?>