<?php


use Silex\Application;
use Doctrine\DBAL\Connection;

/**
 * class PostsModel
 */
class PostsModel extends BaseModel
{
	/**
	 * Returns an arraay of posts
	 * @param  string $type The type of posts to fetch (defaults to NEWS)
	 * @param  integer $count
	 * @param  string $order
	 * @param  integer $user
	 * @return array
	 */
	public function getPosts($category = "NEWS", $count = 10, $order = "DATE_DESC", $user = 1)
	{
		
		//$sql = "SELECT * FROM post WHERE category = ? AND display = 1";
		$sql = "SELECT post.id as id, title, content, category, time, allow_comments, slug, username, user_id
					FROM post
					JOIN user ON user.id = post.user_id
					WHERE category =  ?
					AND display =1";

		$posts = $this->db->fetchAll($sql, array((string) $category));					

		
		

		// need to loop through each record and check if the post can be edited by the current user
		foreach($posts as &$post) 
		{
			if ($this->app['security']->isGranted('ROLE_USER')) {
				$user = $this->app['security']->getToken()->getUser();
				$post['can_edit'] = (( in_array('ROLE_ADMIN', $user->getRoles() )) || ($post['user_id'] == $user->getID()));
			}
			else $post['can_edit'] = false;
		}
		
    	return $posts;
	}
}

